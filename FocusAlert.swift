import Cocoa
import SwiftUI
import IOKit.ps
import IOKit.pwr_mgt
import ServiceManagement
import Carbon

// MARK: - Models

struct TaskTimer: Identifiable {
    let id = UUID()
    var name: String
    var targetDate: Date
    
    var remainingSeconds: TimeInterval {
        return max(0, targetDate.timeIntervalSinceNow)
    }
    
    var formattedTime: String {
        let total = Int(ceil(remainingSeconds))
        let mins = total / 60
        let secs = total % 60
        return String(format: "%02d:%02d", mins, secs)
    }
}

struct Alarm: Codable, Identifiable {
    var id = UUID()
    var hour: Int // 0..23
    var minute: Int // 0..59
    var label: String
    var repeatDays: Set<Int> // 1=Sun, 2=Mon, ..., 7=Sat (matching Calendar weekday)
    var isEnabled: Bool = true
    var lastFiredDate: Date? = nil
    
    var timeString: String {
        let h = hour % 12 == 0 ? 12 : hour % 12
        let ampm = hour < 12 ? "AM" : "PM"
        return String(format: "%d:%02d %@", h, minute, ampm)
    }
    
    var repeatSummary: String {
        if repeatDays.isEmpty { return "Once" }
        if repeatDays.count == 7 { return "Every day" }
        if repeatDays == Set([2, 3, 4, 5, 6]) { return "Weekdays" }
        let dayNames = [2: "Mon", 3: "Tue", 4: "Wed", 5: "Thu", 6: "Fri", 7: "Sat", 1: "Sun"]
        let sortedOrder = [2, 3, 4, 5, 6, 7, 1]
        let sorted = sortedOrder.filter { repeatDays.contains($0) }
        return sorted.compactMap { dayNames[$0] }.joined(separator: ", ")
    }
}

enum KeepAwakeOption: Int, CaseIterable {
    case off = 0
    case min30 = 30
    case hr1 = 60
    case hr2 = 120
    case indefinitely = -1
    
    var title: String {
        switch self {
        case .off: return "Off"
        case .min30: return "30 min"
        case .hr1: return "1 hr"
        case .hr2: return "2 hr"
        case .indefinitely: return "Indefinitely"
        }
    }
    
    var durationSeconds: TimeInterval? {
        switch self {
        case .off: return nil
        case .min30: return 1800
        case .hr1: return 3600
        case .hr2: return 7200
        case .indefinitely: return nil
        }
    }
}

enum OverlayType {
    case taskTimer(taskNames: [String])
    case alarm(timeString: String, label: String)
    case claudeReset(accountNames: [String], nextResetStr: String, sessionLengthHours: Int)
    case fullBattery(percent: Int)
    case lowBattery(percent: Int)
    case test
}

// MARK: - Full Screen Overlay Window & SwiftUI View

class FullScreenOverlayWindow: NSWindow {
    var onReturnPressed: (() -> Void)?
    
    override var canBecomeKey: Bool { return true }
    override var canBecomeMain: Bool { return true }
    
    override func keyDown(with event: NSEvent) {
        // Keycode 36 = Return, 76 = Enter, 53 = Escape
        if event.keyCode == 36 || event.keyCode == 76 || event.keyCode == 53 {
            if let action = onReturnPressed {
                action()
                return
            }
        }
        super.keyDown(with: event)
    }
}

struct OverlayView: View {
    let type: OverlayType
    let primaryTitle: String
    let secondaryTitle: String?
    let onPrimary: () -> Void
    let onSecondary: (() -> Void)?
    
    var backgroundColor: Color {
        switch type {
        case .taskTimer:
            return Color(nsColor: NSColor(red: 0.85, green: 0.15, blue: 0.15, alpha: 0.94))
        case .alarm:
            return Color(nsColor: NSColor(red: 0.15, green: 0.35, blue: 0.85, alpha: 0.94))
        case .claudeReset:
            return Color(nsColor: NSColor(red: 0.5, green: 0.2, blue: 0.8, alpha: 0.94))
        case .fullBattery:
            return Color(nsColor: NSColor(red: 0.15, green: 0.65, blue: 0.25, alpha: 0.94))
        case .lowBattery:
            return Color(nsColor: NSColor(red: 0.9, green: 0.45, blue: 0.1, alpha: 0.94))
        case .test:
            return Color(nsColor: NSColor(red: 0.4, green: 0.2, blue: 0.6, alpha: 0.94))
        }
    }
    
    var iconName: String {
        switch type {
        case .taskTimer: return "timer"
        case .alarm: return "alarm.fill"
        case .claudeReset: return "sparkles"
        case .fullBattery: return "battery.100.bolt"
        case .lowBattery: return "battery.25"
        case .test: return "bell.badge.fill"
        }
    }
    
    var titleText: String {
        switch type {
        case .taskTimer:
            return "Time's up!"
        case .alarm(let timeStr, _):
            return timeStr
        case .claudeReset(let names, _, _):
            return "Claude ready: \(names.joined(separator: ", "))"
        case .fullBattery(let percent):
            return "Battery \(percent)% — Unplug charger"
        case .lowBattery(let percent):
            return "Battery \(percent)% — Plug in charger"
        case .test:
            return "Test Alert"
        }
    }
    
    var messageText: String {
        switch type {
        case .taskTimer(let names):
            return names.joined(separator: ", ")
        case .alarm(_, let label):
            return label
        case .claudeReset(_, let nextResetStr, let sessionLength):
            return "Your \(sessionLength)-hour usage limit has reset.\nNext reset: \(nextResetStr)"
        case .fullBattery:
            return "Your Mac battery is fully charged. Unplug the power adapter."
        case .lowBattery:
            return "Your battery level is low. Connect your Mac to power."
        case .test:
            return "This is a full-screen overlay test notification."
        }
    }
    
    var body: some View {
        ZStack {
            backgroundColor
                .edgesIgnoringSafeArea(.all)
            
            VStack(spacing: 28) {
                Spacer()
                
                Image(systemName: iconName)
                    .font(.system(size: 88, weight: .bold))
                    .foregroundColor(.white)
                    .shadow(color: .black.opacity(0.3), radius: 10, x: 0, y: 5)
                
                Text(titleText)
                    .font(.system(size: 48, weight: .bold, design: .rounded))
                    .foregroundColor(.white)
                    .multilineTextAlignment(.center)
                    .padding(.horizontal, 40)
                    .shadow(color: .black.opacity(0.3), radius: 8, x: 0, y: 4)
                
                Text(messageText)
                    .font(.system(size: 24, weight: .medium))
                    .foregroundColor(.white.opacity(0.95))
                    .multilineTextAlignment(.center)
                    .padding(.horizontal, 60)
                
                HStack(spacing: 20) {
                    Button(action: onPrimary) {
                        Text(primaryTitle)
                            .font(.system(size: 20, weight: .bold))
                            .padding(.horizontal, 36)
                            .padding(.vertical, 14)
                            .background(Color.white)
                            .foregroundColor(.black)
                            .cornerRadius(12)
                            .shadow(color: .black.opacity(0.2), radius: 5, x: 0, y: 3)
                    }
                    .buttonStyle(.plain)
                    .keyboardShortcut(.defaultAction)
                    
                    if let secondaryTitle = secondaryTitle, let onSecondary = onSecondary {
                        Button(action: onSecondary) {
                            Text(secondaryTitle)
                                .font(.system(size: 20, weight: .semibold))
                                .padding(.horizontal, 30)
                                .padding(.vertical, 14)
                                .background(Color.white.opacity(0.2))
                                .foregroundColor(.white)
                                .cornerRadius(12)
                                .overlay(
                                    RoundedRectangle(cornerRadius: 12)
                                        .stroke(Color.white.opacity(0.6), lineWidth: 1.5)
                                )
                        }
                        .buttonStyle(.plain)
                    }
                }
                .padding(.top, 10)
                
                Spacer()
                
                Text("Press Return to dismiss")
                    .font(.system(size: 14, weight: .medium))
                    .foregroundColor(.white.opacity(0.65))
                    .padding(.bottom, 24)
            }
        }
    }
}

@MainActor
class OverlayManager {
    static let shared = OverlayManager()
    
    private var windows: [FullScreenOverlayWindow] = []
    private var currentSound: NSSound? = nil
    
    func showOverlay(
        type: OverlayType,
        soundEnabled: Bool,
        isPresentationMode: Bool,
        primaryTitle: String = "OK",
        secondaryTitle: String? = nil,
        onPrimary: @escaping @MainActor () -> Void,
        onSecondary: (@MainActor () -> Void)? = nil
    ) {
        dismissOverlay()
        
        let primaryAction: () -> Void = { [weak self] in
            DispatchQueue.main.async {
                self?.dismissOverlay()
                onPrimary()
            }
        }
        
        let secondaryAction: (() -> Void)? = onSecondary != nil ? { [weak self] in
            DispatchQueue.main.async {
                self?.dismissOverlay()
                onSecondary?()
            }
        } : nil
        
        let screens = NSScreen.screens
        guard !screens.isEmpty else { return }
        
        var createdWindows: [FullScreenOverlayWindow] = []
        
        for screen in screens {
            let window = FullScreenOverlayWindow(
                contentRect: screen.frame,
                styleMask: [.borderless],
                backing: .buffered,
                defer: false,
                screen: screen
            )
            
            window.level = .screenSaver
            window.collectionBehavior = [.canJoinAllSpaces, .fullScreenAuxiliary, .stationary]
            window.backgroundColor = .clear
            window.isOpaque = false
            window.hasShadow = false
            window.isReleasedWhenClosed = false
            window.setFrame(screen.frame, display: true)
            window.onReturnPressed = { primaryAction() }
            
            let view = OverlayView(
                type: type,
                primaryTitle: primaryTitle,
                secondaryTitle: secondaryTitle,
                onPrimary: primaryAction,
                onSecondary: secondaryAction
            )
            
            window.contentView = NSHostingView(rootView: view)
            window.makeKeyAndOrderFront(nil)
            createdWindows.append(window)
        }
        
        self.windows = createdWindows
        NSApp.activate(ignoringOtherApps: true)
        
        // Sound logic: sound enabled and not muted by presentation mode for timer/alarm/claudeReset
        let allowSound: Bool
        switch type {
        case .taskTimer, .alarm, .claudeReset:
            allowSound = soundEnabled && !isPresentationMode
        case .fullBattery, .lowBattery, .test:
            allowSound = soundEnabled && !isPresentationMode
        }
        
        if allowSound {
            if let sound = NSSound(named: "Glass") {
                sound.loops = true
                sound.play()
                self.currentSound = sound
            }
        }
    }
    
    func dismissOverlay() {
        if let sound = currentSound {
            sound.stop()
            currentSound = nil
        }
        
        let currentWindows = windows
        windows.removeAll()
        
        DispatchQueue.main.async {
            for win in currentWindows {
                win.orderOut(nil)
                win.close()
            }
        }
    }
}

// MARK: - Claude Limit Reminders Models

struct ClaudeAccount: Codable, Identifiable {
    var id = UUID()
    var name: String
    var nextResetDate: Date? = nil
    var isAutoRenewEnabled: Bool = true
    var isPaused: Bool = false
    var lastFiredDate: Date? = nil
    
    var statusSummary: String {
        if isPaused { return "\(name) — paused" }
        guard let reset = nextResetDate else { return "\(name) — not set" }
        
        let remaining = reset.timeIntervalSinceNow
        if remaining <= 0 {
            return "\(name) — resetting..."
        }
        
        let formatter = DateFormatter()
        let calendar = Calendar.current
        if calendar.isDateInToday(reset) {
            formatter.dateFormat = "h:mm a"
        } else {
            formatter.dateFormat = "EEE h:mm a"
        }
        let timeStr = formatter.string(from: reset)
        
        let totalSecs = Int(ceil(remaining))
        let hours = totalSecs / 3600
        let mins = (totalSecs % 3600) / 60
        let timeRemStr: String
        if hours > 0 {
            timeRemStr = "\(hours)h \(mins)m"
        } else {
            timeRemStr = "\(mins)m"
        }
        
        return "\(name) — next reset \(timeStr) (\(timeRemStr))"
    }
}

class ClaudeQuickResetObject: NSObject {
    let accountId: UUID
    let minutes: Int
    
    init(accountId: UUID, minutes: Int) {
        self.accountId = accountId
        self.minutes = minutes
        super.init()
    }
}

// MARK: - Helper Managers

struct BatteryStatus {
    let hasBattery: Bool
    let percent: Int
    let isPluggedIn: Bool
    let isCharging: Bool
    
    var description: String {
        guard hasBattery else { return "No battery" }
        if isCharging {
            return "Battery: \(percent)% (Charging)"
        } else if isPluggedIn {
            return "Battery: \(percent)% (Charged)"
        } else {
            return "Battery: \(percent)% (Discharging)"
        }
    }
}

class BatteryReader {
    static func getStatus() -> BatteryStatus {
        guard let snapshot = IOPSCopyPowerSourcesInfo()?.takeRetainedValue(),
              let sources = IOPSCopyPowerSourcesList(snapshot)?.takeRetainedValue() as? [CFTypeRef] else {
            return BatteryStatus(hasBattery: false, percent: 0, isPluggedIn: false, isCharging: false)
        }
        
        for source in sources {
            guard let desc = IOPSGetPowerSourceDescription(snapshot, source)?.takeUnretainedValue() as? [String: Any] else {
                continue
            }
            if let type = desc[kIOPSTypeKey] as? String, type == kIOPSInternalBatteryType {
                let currentCap = desc[kIOPSCurrentCapacityKey] as? Int ?? 0
                let maxCap = desc[kIOPSMaxCapacityKey] as? Int ?? 100
                let percent = maxCap > 0 ? Int((Double(currentCap) / Double(maxCap)) * 100.0) : currentCap
                let state = desc[kIOPSPowerSourceStateKey] as? String
                let isPlugged = (state == kIOPSACPowerValue)
                let isCharging = desc[kIOPSIsChargingKey] as? Bool ?? false
                return BatteryStatus(hasBattery: true, percent: percent, isPluggedIn: isPlugged, isCharging: isCharging)
            }
        }
        
        return BatteryStatus(hasBattery: false, percent: 0, isPluggedIn: false, isCharging: false)
    }
}

@MainActor
class SystemActionsManager {
    static func lockScreen() {
        let libPath = "/System/Library/PrivateFrameworks/login.framework/Versions/A/login"
        if let handle = dlopen(libPath, RTLD_LAZY) {
            defer { dlclose(handle) }
            if let sym = dlsym(handle, "SACLockScreenImmediate") {
                typealias SACLockScreenImmediateFunc = @convention(c) () -> Void
                let lockFunc = unsafeBitCast(sym, to: SACLockScreenImmediateFunc.self)
                lockFunc()
                return
            }
        }
        
        let process = Process()
        process.executableURL = URL(fileURLWithPath: "/System/Library/CoreServices/Menu Extras/User.menu/Contents/Resources/CGSession")
        process.arguments = ["-suspend"]
        try? process.run()
    }
    
    static func sleepDisplay() {
        let process = Process()
        process.executableURL = URL(fileURLWithPath: "/usr/bin/pmset")
        process.arguments = ["displaysleepnow"]
        try? process.run()
    }
    
    static func toggleDarkMode() {
        let script = """
        tell application "System Events"
            tell appearance preferences
                set dark mode to not dark mode
            end tell
        end tell
        """
        runAppleScript(script, failureTitle: "Toggle Dark Mode Failed")
    }
    
    static func setDesktopIconsHidden(_ hidden: Bool) {
        let process = Process()
        process.executableURL = URL(fileURLWithPath: "/usr/bin/defaults")
        process.arguments = ["write", "com.apple.finder", "CreateDesktop", "-bool", hidden ? "false" : "true"]
        try? process.run()
        process.waitUntilExit()
        
        let killProcess = Process()
        killProcess.executableURL = URL(fileURLWithPath: "/usr/bin/killall")
        killProcess.arguments = ["Finder"]
        try? killProcess.run()
    }
    
    static func isDesktopIconsHidden() -> Bool {
        let process = Process()
        let pipe = Pipe()
        process.executableURL = URL(fileURLWithPath: "/usr/bin/defaults")
        process.arguments = ["read", "com.apple.finder", "CreateDesktop"]
        process.standardOutput = pipe
        try? process.run()
        process.waitUntilExit()
        
        let data = pipe.fileHandleForReading.readDataToEndOfFile()
        if let output = String(data: data, encoding: .utf8)?.trimmingCharacters(in: .whitespacesAndNewlines) {
            return output == "0" || output.lowercased() == "false"
        }
        return false
    }
    
    static func emptyTrash() {
        let alert = NSAlert()
        alert.messageText = "Empty Trash?"
        alert.informativeText = "Are you sure you want to permanently erase the items in the Trash?"
        alert.addButton(withTitle: "Empty Trash")
        alert.addButton(withTitle: "Cancel")
        alert.alertStyle = .warning
        
        if alert.runModal() == .alertFirstButtonReturn {
            let script = "tell application \"Finder\" to empty trash"
            runAppleScript(script, failureTitle: "Empty Trash Failed")
        }
    }
    
    static func toggleMute() {
        let script = """
        set isMuted to output muted of (get volume settings)
        set volume output muted (not isMuted)
        """
        runAppleScript(script, failureTitle: "Mute/Unmute Failed")
    }
    
    static func isMuted() -> Bool {
        let script = "return output muted of (get volume settings)"
        var error: NSDictionary?
        if let appleScript = NSAppleScript(source: script) {
            let result = appleScript.executeAndReturnError(&error)
            if error == nil {
                return result.booleanValue
            }
        }
        return false
    }
    
    static func setMuted(_ mute: Bool) {
        let script = "set volume output muted \(mute ? "true" : "false")"
        runAppleScript(script, failureTitle: "Set Mute State Failed")
    }
    
    static func runAppleScript(_ source: String, failureTitle: String) {
        var errorInfo: NSDictionary?
        if let script = NSAppleScript(source: source) {
            script.executeAndReturnError(&errorInfo)
            if let error = errorInfo {
                let errorMsg = error[NSAppleScript.errorMessage] as? String ?? "Unknown AppleScript error"
                showAutomationErrorAlert(title: failureTitle, detail: errorMsg)
            }
        }
    }
    
    static func showAutomationErrorAlert(title: String, detail: String) {
        let alert = NSAlert()
        alert.messageText = title
        alert.informativeText = "FocusAlert needs Automation permission to control system preferences and Finder.\n\nPlease grant permission in System Settings → Privacy & Security → Automation.\n\nError details: \(detail)"
        alert.alertStyle = .warning
        alert.addButton(withTitle: "OK")
        alert.runModal()
    }
}

class AlarmDialogHelper: NSObject {
    weak var popUp: NSPopUpButton?
    var dayButtons: [NSButton] = []
    
    init(popUp: NSPopUpButton, dayButtons: [NSButton]) {
        self.popUp = popUp
        self.dayButtons = dayButtons
    }
    
    @objc func repeatChanged(_ sender: NSPopUpButton) {
        updateDaysState(index: sender.indexOfSelectedItem)
    }
    
    func updateDaysState(index: Int, initialSet: Set<Int>? = nil) {
        switch index {
        case 0: // Once
            for btn in dayButtons {
                btn.state = .off
                btn.isEnabled = false
            }
        case 1: // Every day
            for btn in dayButtons {
                btn.state = .on
                btn.isEnabled = false
            }
        case 2: // Weekdays
            for btn in dayButtons {
                btn.state = (btn.tag >= 2 && btn.tag <= 6) ? .on : .off
                btn.isEnabled = false
            }
        case 3: // Custom
            for btn in dayButtons {
                if let set = initialSet {
                    btn.state = set.contains(btn.tag) ? .on : .off
                }
                btn.isEnabled = true
            }
        default:
            break
        }
    }
}

class CarbonHotKeyManager {
    static let shared = CarbonHotKeyManager()
    
    private var registeredRefs: [EventHotKeyRef?] = []
    private var eventHandlerRef: EventHandlerRef? = nil
    
    func registerHotKeys(onTrigger: @escaping (UInt32) -> Void) {
        unregisterHotKeys()
        
        var eventType = EventTypeSpec(eventClass: OSType(kEventClassKeyboard), eventKind: UInt32(kEventHotKeyPressed))
        
        let status = InstallEventHandler(GetApplicationEventTarget(), { _, eventRef, _ in
            guard let eventRef = eventRef else { return noErr }
            var hkID = EventHotKeyID()
            let err = GetEventParameter(eventRef, EventParamName(kEventParamDirectObject), EventParamType(typeEventHotKeyID), nil, MemoryLayout<EventHotKeyID>.size, nil, &hkID)
            if err == noErr {
                let id = hkID.id
                DispatchQueue.main.async {
                    CarbonHotKeyManager.shared.handleTrigger(id: id)
                }
            }
            return noErr
        }, 1, &eventType, nil, &eventHandlerRef)
        
        if status != noErr {
            print("Failed to install Carbon event handler: \(status)")
        }
        
        self.triggerCallback = onTrigger
        
        // Shortcuts:
        // 1: ⌃⌥T Add Task Timer
        // 2: ⌃⌥A Add Alarm
        // 3: ⌃⌥Q Quit All Apps
        // 4: ⌃⌥H Hide All Apps
        // 5: ⌃⌥L Lock Screen
        // 6: ⌃⌥D Sleep Display
        // 7: ⌃⌥P Presentation Mode
        let shortcuts: [(UInt32, UInt32, UInt32)] = [
            (UInt32(kVK_ANSI_T), UInt32(controlKey | optionKey), 1),
            (UInt32(kVK_ANSI_A), UInt32(controlKey | optionKey), 2),
            (UInt32(kVK_ANSI_Q), UInt32(controlKey | optionKey), 3),
            (UInt32(kVK_ANSI_H), UInt32(controlKey | optionKey), 4),
            (UInt32(kVK_ANSI_L), UInt32(controlKey | optionKey), 5),
            (UInt32(kVK_ANSI_D), UInt32(controlKey | optionKey), 6),
            (UInt32(kVK_ANSI_P), UInt32(controlKey | optionKey), 7)
        ]
        
        for (code, mods, id) in shortcuts {
            var ref: EventHotKeyRef?
            let hkID = EventHotKeyID(signature: OSType(1179862354) /* 'FCAT' */, id: id)
            let regErr = RegisterEventHotKey(code, mods, hkID, GetApplicationEventTarget(), 0, &ref)
            if regErr == noErr {
                registeredRefs.append(ref)
            }
        }
    }
    
    private var triggerCallback: ((UInt32) -> Void)?
    
    private func handleTrigger(id: UInt32) {
        triggerCallback?(id)
    }
    
    func unregisterHotKeys() {
        for ref in registeredRefs {
            if let ref = ref {
                UnregisterEventHotKey(ref)
            }
        }
        registeredRefs.removeAll()
        if let handler = eventHandlerRef {
            RemoveEventHandler(handler)
            eventHandlerRef = nil
        }
    }
}

// MARK: - Main Application Delegate

@MainActor
final class AppDelegate: NSObject, NSApplicationDelegate, NSMenuDelegate {
    static weak var shared: AppDelegate?
    
    private var statusItem: NSStatusItem!
    private var statusMenu: NSMenu!
    
    // State
    private var taskTimers: [TaskTimer] = []
    private var alarms: [Alarm] = []
    private var claudeAccounts: [ClaudeAccount] = []
    private var batteryStatus: BatteryStatus = BatteryStatus(hasBattery: false, percent: 0, isPluggedIn: false, isCharging: false)
    
    // Timers
    private var tickTimer: Timer?
    private var batteryTickCounter = 0
    
    // Power Management Assertions
    private var keepAwakeAssertionID: IOPMAssertionID = 0
    private var alarmSleepAssertionID: IOPMAssertionID = 0
    
    // Keep Awake state
    private var keepAwakeMode: KeepAwakeOption = .off
    private var keepAwakeEndDate: Date? = nil
    
    // Presentation Mode state
    private var isPresentationMode: Bool = false
    private var savedDesktopIconsHidden: Bool? = nil
    private var savedKeepAwakeMode: KeepAwakeOption? = nil
    private var savedMuteState: Bool? = nil
    
    // Battery Alerts state
    private var hasFiredFullBatteryAlert = false
    private var hasFiredLowBatteryAlert = false
    
    // Frontmost app before menu opened
    private var appBeforeMenuOpened: NSRunningApplication? = nil
    
    // UserDefaults keys
    private let kAlarmsKey = "focusalert_saved_alarms"
    private let kClaudeAccountsKey = "focusalert_saved_claude_accounts"
    private let kClaudeSessionLengthKey = "focusalert_claude_session_length"
    private let kAlertSoundKey = "focusalert_alert_sound"
    private let kSnoozeLengthKey = "focusalert_snooze_length"
    private let kKeepAwakeAlarmsKey = "focusalert_keep_awake_alarms"
    private let kShortcutsKey = "focusalert_shortcuts_enabled"
    private let kBatteryAlertsKey = "focusalert_battery_alerts_enabled"
    private let kFullThresholdKey = "focusalert_full_threshold"
    private let kLowThresholdKey = "focusalert_low_threshold"
    
    func applicationDidFinishLaunching(_ notification: Notification) {
        AppDelegate.shared = self
        
        setupHiddenMainMenu()
        loadUserDefaults()
        setupStatusItem()
        
        // Start 1-second timer tick in .common mode so it ticks during menu interaction
        let timer = Timer(timeInterval: 1.0, target: self, selector: #selector(onTick), userInfo: nil, repeats: true)
        RunLoop.main.add(timer, forMode: .common)
        self.tickTimer = timer
        
        // Initial battery check
        updateBatteryStatus()
        
        // Register hotkeys if enabled
        if isShortcutsEnabled {
            setupHotKeys()
        }
        
        // Update alarm assertion
        updateAlarmSleepAssertion()
    }
    
    func applicationWillTerminate(_ notification: Notification) {
        releaseKeepAwakeAssertion()
        releaseAlarmSleepAssertion()
        CarbonHotKeyManager.shared.unregisterHotKeys()
    }
    
    func applicationShouldTerminateAfterLastWindowClosed(_ sender: NSApplication) -> Bool {
        return false
    }
    
    // MARK: - Hidden Main Menu for Cmd+C/V/X/A
    
    private func setupHiddenMainMenu() {
        let mainMenu = NSMenu()
        let editMenuItem = NSMenuItem()
        let editMenu = NSMenu(title: "Edit")
        
        editMenu.addItem(withTitle: "Undo", action: Selector(("undo:")), keyEquivalent: "z")
        editMenu.addItem(withTitle: "Redo", action: Selector(("redo:")), keyEquivalent: "Z")
        editMenu.addItem(NSMenuItem.separator())
        editMenu.addItem(withTitle: "Cut", action: #selector(NSText.cut(_:)), keyEquivalent: "x")
        editMenu.addItem(withTitle: "Copy", action: #selector(NSText.copy(_:)), keyEquivalent: "c")
        editMenu.addItem(withTitle: "Paste", action: #selector(NSText.paste(_:)), keyEquivalent: "v")
        editMenu.addItem(withTitle: "Select All", action: #selector(NSText.selectAll(_:)), keyEquivalent: "a")
        
        editMenuItem.submenu = editMenu
        mainMenu.addItem(editMenuItem)
        NSApp.mainMenu = mainMenu
    }
    
    // MARK: - UserDefaults & Settings
    
    private var alertSoundEnabled: Bool {
        get { UserDefaults.standard.object(forKey: kAlertSoundKey) as? Bool ?? true }
        set { UserDefaults.standard.set(newValue, forKey: kAlertSoundKey) }
    }
    
    private var snoozeLengthMinutes: Int {
        get { UserDefaults.standard.object(forKey: kSnoozeLengthKey) as? Int ?? 5 }
        set { UserDefaults.standard.set(newValue, forKey: kSnoozeLengthKey) }
    }
    
    private var keepAwakeForAlarmsEnabled: Bool {
        get { UserDefaults.standard.object(forKey: kKeepAwakeAlarmsKey) as? Bool ?? false }
        set {
            UserDefaults.standard.set(newValue, forKey: kKeepAwakeAlarmsKey)
            updateAlarmSleepAssertion()
        }
    }
    
    private var isShortcutsEnabled: Bool {
        get { UserDefaults.standard.object(forKey: kShortcutsKey) as? Bool ?? true }
        set {
            UserDefaults.standard.set(newValue, forKey: kShortcutsKey)
            if newValue {
                setupHotKeys()
            } else {
                CarbonHotKeyManager.shared.unregisterHotKeys()
            }
        }
    }
    
    private var isBatteryAlertsEnabled: Bool {
        get { UserDefaults.standard.object(forKey: kBatteryAlertsKey) as? Bool ?? true }
        set { UserDefaults.standard.set(newValue, forKey: kBatteryAlertsKey) }
    }
    
    private var fullBatteryThreshold: Int {
        get { UserDefaults.standard.object(forKey: kFullThresholdKey) as? Int ?? 100 }
        set { UserDefaults.standard.set(newValue, forKey: kFullThresholdKey) }
    }
    
    private var lowBatteryThreshold: Int {
        get { UserDefaults.standard.object(forKey: kLowThresholdKey) as? Int ?? 20 }
        set { UserDefaults.standard.set(newValue, forKey: kLowThresholdKey) }
    }
    
    private var claudeSessionLengthHours: Int {
        get { UserDefaults.standard.object(forKey: kClaudeSessionLengthKey) as? Int ?? 5 }
        set { UserDefaults.standard.set(newValue, forKey: kClaudeSessionLengthKey) }
    }
    
    private func loadUserDefaults() {
        if let data = UserDefaults.standard.data(forKey: kAlarmsKey),
           let decoded = try? JSONDecoder().decode([Alarm].self, from: data) {
            self.alarms = decoded
        }
        
        if let data = UserDefaults.standard.data(forKey: kClaudeAccountsKey),
           let decoded = try? JSONDecoder().decode([ClaudeAccount].self, from: data) {
            self.claudeAccounts = decoded
        } else {
            self.claudeAccounts = [
                ClaudeAccount(name: "Personal"),
                ClaudeAccount(name: "Work")
            ]
        }
    }
    
    private func saveAlarms() {
        if let data = try? JSONEncoder().encode(alarms) {
            UserDefaults.standard.set(data, forKey: kAlarmsKey)
        }
        updateAlarmSleepAssertion()
    }
    
    private func saveClaudeAccounts() {
        if let data = try? JSONEncoder().encode(claudeAccounts) {
            UserDefaults.standard.set(data, forKey: kClaudeAccountsKey)
        }
    }
    
    // MARK: - HotKeys Setup
    
    private func setupHotKeys() {
        CarbonHotKeyManager.shared.registerHotKeys { [weak self] id in
            guard let self = self else { return }
            switch id {
            case 1: self.actionAddTaskTimer()
            case 2: self.actionAddAlarm()
            case 3: self.actionQuitAllApps()
            case 4: self.actionHideAllApps()
            case 5: SystemActionsManager.lockScreen()
            case 6: SystemActionsManager.sleepDisplay()
            case 7: self.actionTogglePresentationMode()
            default: break
            }
        }
    }
    
    // MARK: - Status Item & Menu Building
    
    private func setupStatusItem() {
        statusItem = NSStatusBar.system.statusItem(withLength: NSStatusItem.variableLength)
        statusMenu = NSMenu()
        statusMenu.delegate = self
        statusItem.menu = statusMenu
        updateStatusItemDisplay()
    }
    
    private func updateStatusItemDisplay() {
        guard let button = statusItem.button else { return }
        
        let hasActiveIndicator = isPresentationMode || (keepAwakeMode != .off)
        let iconName = hasActiveIndicator ? "bell.badge" : "timer"
        
        button.image = NSImage(systemSymbolName: iconName, accessibilityDescription: "FocusAlert")
        button.imagePosition = .imageLeft
        
        let activeTimers = taskTimers.filter { $0.remainingSeconds > 0 }
        if activeTimers.isEmpty {
            button.title = ""
            button.attributedTitle = NSAttributedString(string: "")
        } else {
            let sorted = activeTimers.sorted { $0.targetDate < $1.targetDate }
            let earliest = sorted[0]
            let extraCount = activeTimers.count - 1
            let extraText = extraCount > 0 ? " +\(extraCount)" : ""
            let fullText = " \(earliest.formattedTime)\(extraText)"
            
            let font = NSFont.monospacedDigitSystemFont(ofSize: NSFont.systemFontSize, weight: .medium)
            button.attributedTitle = NSAttributedString(string: fullText, attributes: [.font: font])
        }
    }
    
    func menuWillOpen(_ menu: NSMenu) {
        appBeforeMenuOpened = NSWorkspace.shared.frontmostApplication
        rebuildMenu()
    }
    
    private func rebuildMenu() {
        statusMenu.removeAllItems()
        
        // 1. Task Timers
        let addTaskItem = NSMenuItem(title: "Add Task Timer…", action: #selector(menuActionAddTaskTimer), keyEquivalent: "t")
        addTaskItem.keyEquivalentModifierMask = [.control, .option]
        addTaskItem.target = self
        statusMenu.addItem(addTaskItem)
        
        let quickTimerMenu = NSMenu()
        let quickMins = [5, 10, 15, 25, 30, 45, 60]
        for m in quickMins {
            let item = NSMenuItem(title: "\(m) minutes", action: #selector(menuActionQuickTimer(_:)), keyEquivalent: "")
            item.tag = m
            item.target = self
            quickTimerMenu.addItem(item)
        }
        let quickTimerItem = NSMenuItem(title: "Quick Timer", action: nil, keyEquivalent: "")
        quickTimerItem.submenu = quickTimerMenu
        statusMenu.addItem(quickTimerItem)
        
        let activeTimers = taskTimers.filter { $0.remainingSeconds > 0 }
        if activeTimers.isEmpty {
            let noTimersItem = NSMenuItem(title: "No active timers", action: nil, keyEquivalent: "")
            noTimersItem.isEnabled = false
            statusMenu.addItem(noTimersItem)
        } else {
            for timer in activeTimers {
                let timerItem = NSMenuItem(title: "\(timer.name) — \(timer.formattedTime)", action: nil, keyEquivalent: "")
                let sub = NSMenu()
                
                let add5 = NSMenuItem(title: "+5 min", action: #selector(menuActionAdd5MinTimer(_:)), keyEquivalent: "")
                add5.representedObject = timer.id
                add5.target = self
                sub.addItem(add5)
                
                let cancel = NSMenuItem(title: "Cancel", action: #selector(menuActionCancelTimer(_:)), keyEquivalent: "")
                cancel.representedObject = timer.id
                cancel.target = self
                sub.addItem(cancel)
                
                timerItem.submenu = sub
                statusMenu.addItem(timerItem)
            }
        }
        
        statusMenu.addItem(NSMenuItem.separator())
        
        // 2. Alarms
        let addAlarmItem = NSMenuItem(title: "Add Alarm…", action: #selector(menuActionAddAlarm), keyEquivalent: "a")
        addAlarmItem.keyEquivalentModifierMask = [.control, .option]
        addAlarmItem.target = self
        statusMenu.addItem(addAlarmItem)
        
        let alarmsMenu = NSMenu()
        if alarms.isEmpty {
            let noAlarms = NSMenuItem(title: "No alarms", action: nil, keyEquivalent: "")
            noAlarms.isEnabled = false
            alarmsMenu.addItem(noAlarms)
        } else {
            for alarm in alarms {
                let title = "\(alarm.timeString) — \(alarm.label) (\(alarm.repeatSummary))"
                let item = NSMenuItem(title: title, action: #selector(menuActionToggleAlarm(_:)), keyEquivalent: "")
                item.state = alarm.isEnabled ? .on : .off
                item.representedObject = alarm.id
                item.target = self
                
                let sub = NSMenu()
                let editItem = NSMenuItem(title: "Edit…", action: #selector(menuActionEditAlarm(_:)), keyEquivalent: "")
                editItem.representedObject = alarm.id
                editItem.target = self
                sub.addItem(editItem)
                
                let deleteItem = NSMenuItem(title: "Delete", action: #selector(menuActionDeleteAlarm(_:)), keyEquivalent: "")
                deleteItem.representedObject = alarm.id
                deleteItem.target = self
                sub.addItem(deleteItem)
                
                item.submenu = sub
                alarmsMenu.addItem(item)
            }
        }
        let alarmsSubItem = NSMenuItem(title: "Alarms", action: nil, keyEquivalent: "")
        alarmsSubItem.submenu = alarmsMenu
        statusMenu.addItem(alarmsSubItem)
        
        let nextAlarmText = getNextAlarmSummary()
        let nextAlarmItem = NSMenuItem(title: "Next alarm: \(nextAlarmText)", action: nil, keyEquivalent: "")
        nextAlarmItem.isEnabled = false
        statusMenu.addItem(nextAlarmItem)
        
        statusMenu.addItem(NSMenuItem.separator())
        
        // 3. Claude Accounts
        let claudeMenu = NSMenu()
        if claudeAccounts.isEmpty {
            let noAccts = NSMenuItem(title: "No accounts", action: nil, keyEquivalent: "")
            noAccts.isEnabled = false
            claudeMenu.addItem(noAccts)
        } else {
            for account in claudeAccounts {
                let item = NSMenuItem(title: account.statusSummary, action: nil, keyEquivalent: "")
                let sub = NSMenu()
                
                let setTimeItem = NSMenuItem(title: "Set next reset time…", action: #selector(menuActionClaudeSetTime(_:)), keyEquivalent: "")
                setTimeItem.representedObject = account.id
                setTimeItem.target = self
                sub.addItem(setTimeItem)
                
                let resetInSub = NSMenu()
                let quickOptions: [(Int, String)] = [(30, "30 min"), (60, "1 h"), (120, "2 h"), (180, "3 h"), (240, "4 h"), (300, "5 h")]
                for (mins, label) in quickOptions {
                    let qItem = NSMenuItem(title: label, action: #selector(menuActionClaudeResetIn(_:)), keyEquivalent: "")
                    qItem.representedObject = ClaudeQuickResetObject(accountId: account.id, minutes: mins)
                    qItem.target = self
                    resetInSub.addItem(qItem)
                }
                let resetInItem = NSMenuItem(title: "Reset in", action: nil, keyEquivalent: "")
                resetInItem.submenu = resetInSub
                sub.addItem(resetInItem)
                
                let startedNow = NSMenuItem(title: "Session started now", action: #selector(menuActionClaudeStartedNow(_:)), keyEquivalent: "")
                startedNow.representedObject = account.id
                startedNow.target = self
                sub.addItem(startedNow)
                
                let pauseTitle = account.isPaused ? "Resume" : "Pause"
                let pauseItem = NSMenuItem(title: pauseTitle, action: #selector(menuActionClaudeTogglePause(_:)), keyEquivalent: "")
                pauseItem.representedObject = account.id
                pauseItem.target = self
                sub.addItem(pauseItem)
                
                let clearItem = NSMenuItem(title: "Clear", action: #selector(menuActionClaudeClear(_:)), keyEquivalent: "")
                clearItem.representedObject = account.id
                clearItem.target = self
                sub.addItem(clearItem)
                
                sub.addItem(NSMenuItem.separator())
                
                let renameItem = NSMenuItem(title: "Rename…", action: #selector(menuActionClaudeRename(_:)), keyEquivalent: "")
                renameItem.representedObject = account.id
                renameItem.target = self
                sub.addItem(renameItem)
                
                let deleteItem = NSMenuItem(title: "Delete", action: #selector(menuActionClaudeDelete(_:)), keyEquivalent: "")
                deleteItem.representedObject = account.id
                deleteItem.target = self
                sub.addItem(deleteItem)
                
                item.submenu = sub
                claudeMenu.addItem(item)
            }
        }
        
        claudeMenu.addItem(NSMenuItem.separator())
        
        let addAcctItem = NSMenuItem(title: "Add Account…", action: #selector(menuActionClaudeAddAccount), keyEquivalent: "")
        addAcctItem.target = self
        claudeMenu.addItem(addAcctItem)
        
        let sessionSub = NSMenu()
        for h in 1...8 {
            let item = NSMenuItem(title: "\(h) h", action: #selector(menuActionClaudeSetSessionLength(_:)), keyEquivalent: "")
            item.tag = h
            item.state = (claudeSessionLengthHours == h) ? .on : .off
            item.target = self
            sessionSub.addItem(item)
        }
        let sessionItem = NSMenuItem(title: "Session Length", action: nil, keyEquivalent: "")
        sessionItem.submenu = sessionSub
        claudeMenu.addItem(sessionItem)
        
        let claudeSubItem = NSMenuItem(title: "Claude Accounts", action: nil, keyEquivalent: "")
        claudeSubItem.submenu = claudeMenu
        statusMenu.addItem(claudeSubItem)
        
        statusMenu.addItem(NSMenuItem.separator())
        
        // 3. Quick Actions
        let qaMenu = NSMenu()
        
        let quitAll = NSMenuItem(title: "Quit All Apps", action: #selector(menuActionQuitAllApps), keyEquivalent: "q")
        quitAll.keyEquivalentModifierMask = [.control, .option]
        quitAll.target = self
        qaMenu.addItem(quitAll)
        
        let quitExcept = NSMenuItem(title: "Quit All Except Current", action: #selector(menuActionQuitAllExceptCurrent), keyEquivalent: "")
        quitExcept.target = self
        qaMenu.addItem(quitExcept)
        
        let forceQuit = NSMenuItem(title: "Force Quit All…", action: #selector(menuActionForceQuitAll), keyEquivalent: "")
        forceQuit.target = self
        qaMenu.addItem(forceQuit)
        
        let hideAll = NSMenuItem(title: "Hide All Apps", action: #selector(menuActionHideAllApps), keyEquivalent: "h")
        hideAll.keyEquivalentModifierMask = [.control, .option]
        hideAll.target = self
        qaMenu.addItem(hideAll)
        
        qaMenu.addItem(NSMenuItem.separator())
        
        let lockItem = NSMenuItem(title: "Lock Screen", action: #selector(menuActionLockScreen), keyEquivalent: "l")
        lockItem.keyEquivalentModifierMask = [.control, .option]
        lockItem.target = self
        qaMenu.addItem(lockItem)
        
        let sleepItem = NSMenuItem(title: "Sleep Display", action: #selector(menuActionSleepDisplay), keyEquivalent: "d")
        sleepItem.keyEquivalentModifierMask = [.control, .option]
        sleepItem.target = self
        qaMenu.addItem(sleepItem)
        
        // Keep Awake Submenu
        let keepAwakeSub = NSMenu()
        for option in KeepAwakeOption.allCases {
            var itemTitle = option.title
            if option == keepAwakeMode, let endDate = keepAwakeEndDate {
                let remaining = max(0, Int(endDate.timeIntervalSinceNow))
                let mins = remaining / 60
                let secs = remaining % 60
                itemTitle += String(format: " (%d:%02d remaining)", mins, secs)
            }
            let item = NSMenuItem(title: itemTitle, action: #selector(menuActionSetKeepAwake(_:)), keyEquivalent: "")
            item.tag = option.rawValue
            item.state = (keepAwakeMode == option) ? .on : .off
            item.target = self
            keepAwakeSub.addItem(item)
        }
        let keepAwakeItem = NSMenuItem(title: "Keep Awake", action: nil, keyEquivalent: "")
        keepAwakeItem.submenu = keepAwakeSub
        qaMenu.addItem(keepAwakeItem)
        
        let darkItem = NSMenuItem(title: "Toggle Dark Mode", action: #selector(menuActionToggleDarkMode), keyEquivalent: "")
        darkItem.target = self
        qaMenu.addItem(darkItem)
        
        let desktopItem = NSMenuItem(title: "Hide Desktop Icons", action: #selector(menuActionToggleDesktopIcons), keyEquivalent: "")
        desktopItem.state = SystemActionsManager.isDesktopIconsHidden() ? .on : .off
        desktopItem.target = self
        qaMenu.addItem(desktopItem)
        
        let muteItem = NSMenuItem(title: "Mute / Unmute", action: #selector(menuActionToggleMute), keyEquivalent: "")
        muteItem.target = self
        qaMenu.addItem(muteItem)
        
        let trashItem = NSMenuItem(title: "Empty Trash…", action: #selector(menuActionEmptyTrash), keyEquivalent: "")
        trashItem.target = self
        qaMenu.addItem(trashItem)
        
        let qaItem = NSMenuItem(title: "Quick Actions", action: nil, keyEquivalent: "")
        qaItem.submenu = qaMenu
        statusMenu.addItem(qaItem)
        
        let presItem = NSMenuItem(title: "Presentation Mode", action: #selector(menuActionTogglePresentationMode), keyEquivalent: "p")
        presItem.keyEquivalentModifierMask = [.control, .option]
        presItem.state = isPresentationMode ? .on : .off
        presItem.target = self
        statusMenu.addItem(presItem)
        
        statusMenu.addItem(NSMenuItem.separator())
        
        // 4. Battery
        let batStatusItem = NSMenuItem(title: batteryStatus.description, action: nil, keyEquivalent: "")
        batStatusItem.isEnabled = false
        statusMenu.addItem(batStatusItem)
        
        let batToggleItem = NSMenuItem(title: "Battery Alerts", action: #selector(menuActionToggleBatteryAlerts), keyEquivalent: "")
        batToggleItem.state = isBatteryAlertsEnabled ? .on : .off
        batToggleItem.target = self
        statusMenu.addItem(batToggleItem)
        
        // Full Battery Submenu
        let fullSub = NSMenu()
        for pct in [80, 85, 90, 95, 100] {
            let item = NSMenuItem(title: "\(pct)%", action: #selector(menuActionSetFullThreshold(_:)), keyEquivalent: "")
            item.tag = pct
            item.state = (fullBatteryThreshold == pct) ? .on : .off
            item.target = self
            fullSub.addItem(item)
        }
        let fullItem = NSMenuItem(title: "Full-charge alert at", action: nil, keyEquivalent: "")
        fullItem.submenu = fullSub
        statusMenu.addItem(fullItem)
        
        // Low Battery Submenu
        let lowSub = NSMenu()
        for pct in [10, 15, 20, 25, 30] {
            let item = NSMenuItem(title: "\(pct)%", action: #selector(menuActionSetLowThreshold(_:)), keyEquivalent: "")
            item.tag = pct
            item.state = (lowBatteryThreshold == pct) ? .on : .off
            item.target = self
            lowSub.addItem(item)
        }
        let lowItem = NSMenuItem(title: "Low-battery alert at", action: nil, keyEquivalent: "")
        lowItem.submenu = lowSub
        statusMenu.addItem(lowItem)
        
        statusMenu.addItem(NSMenuItem.separator())
        
        // 5. Settings
        let settingsMenu = NSMenu()
        
        let soundItem = NSMenuItem(title: "Alert Sound", action: #selector(menuActionToggleAlertSound), keyEquivalent: "")
        soundItem.state = alertSoundEnabled ? .on : .off
        soundItem.target = self
        settingsMenu.addItem(soundItem)
        
        let snoozeSub = NSMenu()
        for len in [5, 10, 15] {
            let item = NSMenuItem(title: "\(len) min", action: #selector(menuActionSetSnoozeLength(_:)), keyEquivalent: "")
            item.tag = len
            item.state = (snoozeLengthMinutes == len) ? .on : .off
            item.target = self
            snoozeSub.addItem(item)
        }
        let snoozeItem = NSMenuItem(title: "Snooze Length", action: nil, keyEquivalent: "")
        snoozeItem.submenu = snoozeSub
        settingsMenu.addItem(snoozeItem)
        
        let keepAwakeAlarmItem = NSMenuItem(title: "Keep Mac Awake for Alarms", action: #selector(menuActionToggleKeepAwakeAlarms), keyEquivalent: "")
        keepAwakeAlarmItem.state = keepAwakeForAlarmsEnabled ? .on : .off
        keepAwakeAlarmItem.target = self
        settingsMenu.addItem(keepAwakeAlarmItem)
        
        let hkItem = NSMenuItem(title: "Keyboard Shortcuts", action: #selector(menuActionToggleShortcuts), keyEquivalent: "")
        hkItem.state = isShortcutsEnabled ? .on : .off
        hkItem.target = self
        settingsMenu.addItem(hkItem)
        
        let loginItem = NSMenuItem(title: "Launch at Login", action: #selector(menuActionToggleLaunchAtLogin), keyEquivalent: "")
        loginItem.state = (SMAppService.mainApp.status == .enabled) ? .on : .off
        loginItem.target = self
        settingsMenu.addItem(loginItem)
        
        let testItem = NSMenuItem(title: "Test Full-Screen Alert", action: #selector(menuActionTestAlert), keyEquivalent: "")
        testItem.target = self
        settingsMenu.addItem(testItem)
        
        let settingsItem = NSMenuItem(title: "Settings", action: nil, keyEquivalent: "")
        settingsItem.submenu = settingsMenu
        statusMenu.addItem(settingsItem)
        
        statusMenu.addItem(NSMenuItem.separator())
        
        // 6. Quit
        let quitItem = NSMenuItem(title: "Quit FocusAlert", action: #selector(menuActionQuit), keyEquivalent: "q")
        quitItem.keyEquivalentModifierMask = [.command]
        quitItem.target = self
        statusMenu.addItem(quitItem)
    }
    
    // MARK: - Main Tick Loop (Fires every second)
    
    @objc private func onTick() {
        updateStatusItemDisplay()
        checkTaskTimers()
        checkAlarms()
        checkClaudeAccounts()
        checkKeepAwakeExpiration()
        
        batteryTickCounter += 1
        if batteryTickCounter >= 30 {
            batteryTickCounter = 0
            updateBatteryStatus()
        }
    }
    
    // MARK: - Task Timers Logic
    
    private func checkTaskTimers() {
        let finished = taskTimers.filter { $0.remainingSeconds <= 0 }
        if !finished.isEmpty {
            taskTimers.removeAll { $0.remainingSeconds <= 0 }
            let names = finished.map { $0.name }
            
            OverlayManager.shared.showOverlay(
                type: .taskTimer(taskNames: names),
                soundEnabled: alertSoundEnabled,
                isPresentationMode: isPresentationMode,
                primaryTitle: "Done",
                secondaryTitle: "Snooze 5 min",
                onPrimary: {},
                onSecondary: { [weak self] in
                    for name in names {
                        self?.addTaskTimer(name: "\(name) (Snoozed)", minutes: 5.0)
                    }
                }
            )
        }
    }
    
    private func addTaskTimer(name: String, minutes: Double) {
        let target = Date().addingTimeInterval(minutes * 60.0)
        let timer = TaskTimer(name: name, targetDate: target)
        taskTimers.append(timer)
        updateStatusItemDisplay()
    }
    
    // MARK: - Alarms Logic & Wake from Sleep
    
    private func checkAlarms() {
        let now = Date()
        let calendar = Calendar.current
        let currentComps = calendar.dateComponents([.year, .month, .day, .weekday], from: now)
        guard let currentWeekday = currentComps.weekday else { return }
        
        for i in 0..<alarms.count {
            var alarm = alarms[i]
            guard alarm.isEnabled else { continue }
            
            // Check if repeatDays match
            let isDayMatch: Bool
            if alarm.repeatDays.isEmpty {
                isDayMatch = true
            } else {
                isDayMatch = alarm.repeatDays.contains(currentWeekday)
            }
            
            guard isDayMatch else { continue }
            
            // Check if already fired in this minute slot
            if let lastFired = alarm.lastFiredDate {
                if calendar.isDate(lastFired, equalTo: now, toGranularity: .minute) {
                    continue
                }
            }
            
            // Calculate scheduled time for today
            var scheduledComps = currentComps
            scheduledComps.hour = alarm.hour
            scheduledComps.minute = alarm.minute
            scheduledComps.second = 0
            
            guard let scheduledDate = calendar.date(from: scheduledComps) else { continue }
            
            // Fire if current time is within 5 minutes after scheduled date (handles exact minute match and wake-from-sleep)
            let diff = now.timeIntervalSince(scheduledDate)
            if diff >= 0 && diff < 300 {
                // Fire alarm!
                alarm.lastFiredDate = now
                if alarm.repeatDays.isEmpty {
                    alarm.isEnabled = false // Once alarms turn off after firing
                }
                alarms[i] = alarm
                saveAlarms()
                
                let timeStr = alarm.timeString
                let label = alarm.label
                let snoozeMins = snoozeLengthMinutes
                
                OverlayManager.shared.showOverlay(
                    type: .alarm(timeString: timeStr, label: label),
                    soundEnabled: alertSoundEnabled,
                    isPresentationMode: isPresentationMode,
                    primaryTitle: "Stop",
                    secondaryTitle: "Snooze",
                    onPrimary: {},
                    onSecondary: { [weak self] in
                        self?.addTaskTimer(name: "Alarm: \(label)", minutes: Double(snoozeMins))
                    }
                )
                break
            }
        }
    }
    
    private func getNextAlarmSummary() -> String {
        let enabledAlarms = alarms.filter { $0.isEnabled }
        guard !enabledAlarms.isEmpty else { return "None" }
        
        let now = Date()
        let calendar = Calendar.current
        var soonestDate: Date? = nil
        
        for alarm in enabledAlarms {
            // Find next date in the upcoming 7 days
            for dayOffset in 0...7 {
                guard let checkDate = calendar.date(byAdding: .day, value: dayOffset, to: now) else { continue }
                let checkComps = calendar.dateComponents([.year, .month, .day, .weekday], from: checkDate)
                guard let weekday = checkComps.weekday else { continue }
                
                if !alarm.repeatDays.isEmpty && !alarm.repeatDays.contains(weekday) {
                    continue
                }
                
                var alarmComps = checkComps
                alarmComps.hour = alarm.hour
                alarmComps.minute = alarm.minute
                alarmComps.second = 0
                
                if let targetDate = calendar.date(from: alarmComps), targetDate > now {
                    if soonestDate == nil || targetDate < soonestDate! {
                        soonestDate = targetDate
                    }
                    break
                }
            }
        }
        
        guard let nextDate = soonestDate else { return "None" }
        
        let formatter = DateFormatter()
        if calendar.isDateInToday(nextDate) {
            formatter.dateFormat = "'Today' h:mm a"
        } else if calendar.isDateInTomorrow(nextDate) {
            formatter.dateFormat = "'Tomorrow' h:mm a"
        } else {
            formatter.dateFormat = "EEE h:mm a"
        }
        return formatter.string(from: nextDate)
    }
    
    // MARK: - Keep Awake & IOPMAssertion
    
    private func setKeepAwakeMode(_ option: KeepAwakeOption) {
        keepAwakeMode = option
        releaseKeepAwakeAssertion()
        
        if option == .off {
            keepAwakeEndDate = nil
        } else if option == .indefinitely {
            keepAwakeEndDate = nil
            acquireKeepAwakeAssertion()
        } else if let duration = option.durationSeconds {
            keepAwakeEndDate = Date().addingTimeInterval(duration)
            acquireKeepAwakeAssertion()
        }
        updateStatusItemDisplay()
    }
    
    private func checkKeepAwakeExpiration() {
        if let endDate = keepAwakeEndDate, Date() >= endDate {
            setKeepAwakeMode(.off)
        }
    }
    
    private func acquireKeepAwakeAssertion() {
        if keepAwakeAssertionID == 0 {
            let type = kIOPMAssertionTypePreventUserIdleDisplaySleep as CFString
            let reason = "FocusAlert Keep Awake" as CFString
            IOPMAssertionCreateWithName(type, IOPMAssertionLevel(kIOPMAssertionLevelOn), reason, &keepAwakeAssertionID)
        }
    }
    
    private func releaseKeepAwakeAssertion() {
        if keepAwakeAssertionID != 0 {
            IOPMAssertionRelease(keepAwakeAssertionID)
            keepAwakeAssertionID = 0
        }
    }
    
    private func updateAlarmSleepAssertion() {
        let hasActiveAlarms = alarms.contains { $0.isEnabled }
        if keepAwakeForAlarmsEnabled && hasActiveAlarms {
            if alarmSleepAssertionID == 0 {
                let type = kIOPMAssertionTypePreventUserIdleSystemSleep as CFString
                let reason = "FocusAlert Alarm Sleep Prevention" as CFString
                IOPMAssertionCreateWithName(type, IOPMAssertionLevel(kIOPMAssertionLevelOn), reason, &alarmSleepAssertionID)
            }
        } else {
            releaseAlarmSleepAssertion()
        }
    }
    
    private func releaseAlarmSleepAssertion() {
        if alarmSleepAssertionID != 0 {
            IOPMAssertionRelease(alarmSleepAssertionID)
            alarmSleepAssertionID = 0
        }
    }
    
    // MARK: - Battery Monitoring & Alert Logic
    
    private func updateBatteryStatus() {
        batteryStatus = BatteryReader.getStatus()
        
        guard batteryStatus.hasBattery else { return }
        
        // Reset flags when charging state changes
        if !batteryStatus.isPluggedIn {
            hasFiredFullBatteryAlert = false
        } else {
            hasFiredLowBatteryAlert = false
        }
        
        guard isBatteryAlertsEnabled && !isPresentationMode else { return }
        
        // Full battery alert
        if batteryStatus.isPluggedIn && batteryStatus.percent >= fullBatteryThreshold {
            if !hasFiredFullBatteryAlert {
                hasFiredFullBatteryAlert = true
                OverlayManager.shared.showOverlay(
                    type: .fullBattery(percent: batteryStatus.percent),
                    soundEnabled: alertSoundEnabled,
                    isPresentationMode: isPresentationMode,
                    primaryTitle: "OK",
                    onPrimary: {}
                )
            }
        }
        
        // Low battery alert
        if !batteryStatus.isPluggedIn && batteryStatus.percent <= lowBatteryThreshold {
            if !hasFiredLowBatteryAlert {
                hasFiredLowBatteryAlert = true
                OverlayManager.shared.showOverlay(
                    type: .lowBattery(percent: batteryStatus.percent),
                    soundEnabled: alertSoundEnabled,
                    isPresentationMode: isPresentationMode,
                    primaryTitle: "OK",
                    onPrimary: {}
                )
            }
        }
    }
    
    // MARK: - Presentation Mode
    
    private func actionTogglePresentationMode() {
        if !isPresentationMode {
            // Turn ON Presentation Mode
            savedDesktopIconsHidden = SystemActionsManager.isDesktopIconsHidden()
            savedKeepAwakeMode = keepAwakeMode
            savedMuteState = SystemActionsManager.isMuted()
            
            isPresentationMode = true
            SystemActionsManager.setDesktopIconsHidden(true)
            setKeepAwakeMode(.indefinitely)
            SystemActionsManager.setMuted(true)
        } else {
            // Turn OFF Presentation Mode
            isPresentationMode = false
            if let savedDesktop = savedDesktopIconsHidden {
                SystemActionsManager.setDesktopIconsHidden(savedDesktop)
            }
            if let savedAwake = savedKeepAwakeMode {
                setKeepAwakeMode(savedAwake)
            }
            if let savedMute = savedMuteState {
                SystemActionsManager.setMuted(savedMute)
            }
        }
        updateStatusItemDisplay()
    }
    
    // MARK: - Menu Actions & Dialogs
    
    @objc private func menuActionAddTaskTimer() { actionAddTaskTimer() }
    @objc private func menuActionAddAlarm() { actionAddAlarm() }
    @objc private func menuActionQuitAllApps() { actionQuitAllApps() }
    @objc private func menuActionHideAllApps() { actionHideAllApps() }
    @objc private func menuActionLockScreen() { SystemActionsManager.lockScreen() }
    @objc private func menuActionSleepDisplay() { SystemActionsManager.sleepDisplay() }
    @objc private func menuActionTogglePresentationMode() { actionTogglePresentationMode() }
    
    private func actionAddTaskTimer() {
        let alert = NSAlert()
        alert.messageText = "Add Task Timer"
        alert.informativeText = "Set a subject and duration for your timer:"
        alert.addButton(withTitle: "Start Timer")
        alert.addButton(withTitle: "Cancel")
        
        if let icon = NSImage(systemSymbolName: "timer", accessibilityDescription: "Task Timer") {
            let config = NSImage.SymbolConfiguration(pointSize: 48, weight: .medium)
            alert.icon = icon.withSymbolConfiguration(config)
        }
        
        let container = NSView(frame: NSRect(x: 0, y: 0, width: 330, height: 85))
        
        let subjectLabel = NSTextField(labelWithString: "Subject:")
        subjectLabel.frame = NSRect(x: 0, y: 52, width: 75, height: 20)
        subjectLabel.alignment = .right
        
        let subjectField = NSTextField(frame: NSRect(x: 82, y: 50, width: 240, height: 24))
        subjectField.placeholderString = "e.g. Deep Work, Review PR"
        
        let durationLabel = NSTextField(labelWithString: "Duration:")
        durationLabel.frame = NSRect(x: 0, y: 15, width: 75, height: 20)
        durationLabel.alignment = .right
        
        let minsField = NSTextField(frame: NSRect(x: 82, y: 13, width: 100, height: 24))
        minsField.stringValue = "25"
        
        let unitLabel = NSTextField(labelWithString: "minutes")
        unitLabel.frame = NSRect(x: 188, y: 15, width: 70, height: 20)
        unitLabel.textColor = .secondaryLabelColor
        
        container.addSubview(subjectLabel)
        container.addSubview(subjectField)
        container.addSubview(durationLabel)
        container.addSubview(minsField)
        container.addSubview(unitLabel)
        
        alert.accessoryView = container
        
        DispatchQueue.main.async {
            alert.window.makeFirstResponder(subjectField)
        }
        
        let response = alert.runModal()
        if response == .alertFirstButtonReturn {
            let rawSubject = subjectField.stringValue.trimmingCharacters(in: .whitespacesAndNewlines)
            let subject = rawSubject.isEmpty ? "Task" : rawSubject
            
            let minsStr = minsField.stringValue.trimmingCharacters(in: .whitespacesAndNewlines)
            if let mins = Double(minsStr), mins > 0 {
                addTaskTimer(name: subject, minutes: mins)
            } else {
                let errAlert = NSAlert()
                errAlert.messageText = "Invalid Duration"
                errAlert.informativeText = "Please enter a valid positive number of minutes."
                errAlert.alertStyle = .warning
                errAlert.runModal()
            }
        }
    }
    
    @objc private func menuActionQuickTimer(_ sender: NSMenuItem) {
        let mins = Double(sender.tag)
        addTaskTimer(name: "\(sender.tag) Min Timer", minutes: mins)
    }
    
    @objc private func menuActionAdd5MinTimer(_ sender: NSMenuItem) {
        if let id = sender.representedObject as? UUID, let idx = taskTimers.firstIndex(where: { $0.id == id }) {
            taskTimers[idx].targetDate = taskTimers[idx].targetDate.addingTimeInterval(300)
            updateStatusItemDisplay()
        }
    }
    
    @objc private func menuActionCancelTimer(_ sender: NSMenuItem) {
        if let id = sender.representedObject as? UUID {
            taskTimers.removeAll { $0.id == id }
            updateStatusItemDisplay()
        }
    }
    
    private func actionAddAlarm() {
        showAddOrEditAlarmDialog(alarmToEdit: nil)
    }
    
    @objc private func menuActionToggleAlarm(_ sender: NSMenuItem) {
        if let id = sender.representedObject as? UUID, let idx = alarms.firstIndex(where: { $0.id == id }) {
            alarms[idx].isEnabled.toggle()
            saveAlarms()
        }
    }
    
    @objc private func menuActionEditAlarm(_ sender: NSMenuItem) {
        if let id = sender.representedObject as? UUID, let alarm = alarms.firstIndex(where: { $0.id == id }) {
            showAddOrEditAlarmDialog(alarmToEdit: alarms[alarm])
        }
    }
    
    @objc private func menuActionDeleteAlarm(_ sender: NSMenuItem) {
        if let id = sender.representedObject as? UUID {
            alarms.removeAll { $0.id == id }
            saveAlarms()
        }
    }
    
    private func showAddOrEditAlarmDialog(alarmToEdit: Alarm?) {
        let isEditing = alarmToEdit != nil
        let alert = NSAlert()
        alert.messageText = isEditing ? "Edit Alarm" : "Add Alarm"
        alert.informativeText = "Set alarm time, subject, and repeat schedule:"
        alert.addButton(withTitle: isEditing ? "Save" : "Add Alarm")
        alert.addButton(withTitle: "Cancel")
        
        if let icon = NSImage(systemSymbolName: "alarm.fill", accessibilityDescription: "Alarm") {
            let config = NSImage.SymbolConfiguration(pointSize: 48, weight: .medium)
            alert.icon = icon.withSymbolConfiguration(config)
        }
        
        let container = NSView(frame: NSRect(x: 0, y: 0, width: 330, height: 160))
        
        // Subject Field
        let subjectLabel = NSTextField(labelWithString: "Subject:")
        subjectLabel.frame = NSRect(x: 0, y: 125, width: 75, height: 20)
        subjectLabel.alignment = .right
        
        let subjectField = NSTextField(frame: NSRect(x: 82, y: 123, width: 240, height: 24))
        subjectField.placeholderString = "e.g. Morning Standup, Take Medicine"
        subjectField.stringValue = alarmToEdit?.label ?? ""
        
        // Time Field
        let timeLabel = NSTextField(labelWithString: "Time:")
        timeLabel.frame = NSRect(x: 0, y: 88, width: 75, height: 20)
        timeLabel.alignment = .right
        
        let datePicker = NSDatePicker(frame: NSRect(x: 82, y: 85, width: 140, height: 26))
        datePicker.datePickerStyle = .textFieldAndStepper
        datePicker.datePickerElements = [.hourMinute]
        
        var calendar = Calendar.current
        calendar.timeZone = TimeZone.current
        var comps = DateComponents()
        if let existing = alarmToEdit {
            comps.hour = existing.hour
            comps.minute = existing.minute
        } else {
            comps.hour = 7
            comps.minute = 30
        }
        if let date = calendar.date(from: comps) {
            datePicker.dateValue = date
        }
        
        // Repeat Dropdown
        let repeatLabel = NSTextField(labelWithString: "Repeat:")
        repeatLabel.frame = NSRect(x: 0, y: 51, width: 75, height: 20)
        repeatLabel.alignment = .right
        
        let popUp = NSPopUpButton(frame: NSRect(x: 82, y: 48, width: 240, height: 26))
        popUp.addItems(withTitles: ["Once", "Every day", "Weekdays", "Custom"])
        
        // Days Row
        let daysLabel = NSTextField(labelWithString: "Days:")
        daysLabel.frame = NSRect(x: 0, y: 14, width: 75, height: 20)
        daysLabel.alignment = .right
        
        let customView = NSView(frame: NSRect(x: 82, y: 10, width: 245, height: 30))
        let dayNames = [("M", 2), ("T", 3), ("W", 4), ("T", 5), ("F", 6), ("S", 7), ("S", 1)]
        var dayButtons: [NSButton] = []
        
        let currentRepeat = alarmToEdit?.repeatDays ?? Set<Int>()
        
        for (idx, (shortName, dayVal)) in dayNames.enumerated() {
            let btn = NSButton(checkboxWithTitle: shortName, target: nil, action: nil)
            btn.frame = NSRect(x: idx * 34, y: 2, width: 32, height: 20)
            btn.tag = dayVal
            dayButtons.append(btn)
            customView.addSubview(btn)
        }
        
        let helper = AlarmDialogHelper(popUp: popUp, dayButtons: dayButtons)
        popUp.target = helper
        popUp.action = #selector(AlarmDialogHelper.repeatChanged(_:))
        
        if isEditing {
            if currentRepeat.isEmpty {
                popUp.selectItem(at: 0)
            } else if currentRepeat.count == 7 {
                popUp.selectItem(at: 1)
            } else if currentRepeat == Set([2, 3, 4, 5, 6]) {
                popUp.selectItem(at: 2)
            } else {
                popUp.selectItem(at: 3)
            }
        } else {
            popUp.selectItem(at: 0)
        }
        
        // Initialize day button states based on selected repeat mode
        helper.updateDaysState(index: popUp.indexOfSelectedItem, initialSet: currentRepeat)
        
        container.addSubview(subjectLabel)
        container.addSubview(subjectField)
        container.addSubview(timeLabel)
        container.addSubview(datePicker)
        container.addSubview(repeatLabel)
        container.addSubview(popUp)
        container.addSubview(daysLabel)
        container.addSubview(customView)
        
        alert.accessoryView = container
        
        DispatchQueue.main.async {
            alert.window.makeFirstResponder(subjectField)
        }
        
        let response = alert.runModal()
        if response == .alertFirstButtonReturn {
            let pickedDate = datePicker.dateValue
            let comps = calendar.dateComponents([.hour, .minute], from: pickedDate)
            let hour = comps.hour ?? 7
            let minute = comps.minute ?? 30
            
            let rawSubject = subjectField.stringValue.trimmingCharacters(in: .whitespacesAndNewlines)
            let subject = rawSubject.isEmpty ? "Alarm" : rawSubject
            
            var repeatDays = Set<Int>()
            switch popUp.indexOfSelectedItem {
            case 0: repeatDays = []
            case 1: repeatDays = Set([1, 2, 3, 4, 5, 6, 7])
            case 2: repeatDays = Set([2, 3, 4, 5, 6])
            case 3:
                for btn in dayButtons {
                    if btn.state == .on {
                        repeatDays.insert(btn.tag)
                    }
                }
            default: break
            }
            
            if isEditing, var existing = alarmToEdit, let idx = alarms.firstIndex(where: { $0.id == existing.id }) {
                existing.hour = hour
                existing.minute = minute
                existing.label = subject
                existing.repeatDays = repeatDays
                alarms[idx] = existing
            } else {
                let newAlarm = Alarm(hour: hour, minute: minute, label: subject, repeatDays: repeatDays, isEnabled: true)
                alarms.append(newAlarm)
            }
            saveAlarms()
        }
    }
    
    // Quick Actions
    
    private func actionQuitAllApps() {
        let apps = NSWorkspace.shared.runningApplications.filter {
            $0.activationPolicy == .regular &&
            $0.bundleIdentifier != "com.apple.finder" &&
            $0 != NSRunningApplication.current
        }
        for app in apps {
            app.terminate()
        }
    }
    
    @objc private func menuActionQuitAllExceptCurrent() {
        guard let frontmost = appBeforeMenuOpened ?? NSWorkspace.shared.frontmostApplication else { return }
        let apps = NSWorkspace.shared.runningApplications.filter {
            $0.activationPolicy == .regular &&
            $0.bundleIdentifier != "com.apple.finder" &&
            $0 != NSRunningApplication.current &&
            $0 != frontmost
        }
        for app in apps {
            app.terminate()
        }
    }
    
    @objc private func menuActionForceQuitAll() {
        let alert = NSAlert()
        alert.messageText = "Force Quit All Applications?"
        alert.informativeText = "This will immediately force quit all open applications. Any unsaved work will be lost."
        alert.addButton(withTitle: "Force Quit")
        alert.addButton(withTitle: "Cancel")
        alert.alertStyle = .critical
        
        if alert.runModal() == .alertFirstButtonReturn {
            let apps = NSWorkspace.shared.runningApplications.filter {
                $0.activationPolicy == .regular &&
                $0.bundleIdentifier != "com.apple.finder" &&
                $0 != NSRunningApplication.current
            }
            for app in apps {
                app.forceTerminate()
            }
        }
    }
    
    private func actionHideAllApps() {
        let apps = NSWorkspace.shared.runningApplications.filter {
            $0.activationPolicy == .regular &&
            $0 != NSRunningApplication.current
        }
        for app in apps {
            app.hide()
        }
    }
    
    @objc private func menuActionSetKeepAwake(_ sender: NSMenuItem) {
        if let option = KeepAwakeOption(rawValue: sender.tag) {
            setKeepAwakeMode(option)
        }
    }
    
    @objc private func menuActionToggleDarkMode() { SystemActionsManager.toggleDarkMode() }
    @objc private func menuActionToggleDesktopIcons() {
        let current = SystemActionsManager.isDesktopIconsHidden()
        SystemActionsManager.setDesktopIconsHidden(!current)
    }
    @objc private func menuActionToggleMute() { SystemActionsManager.toggleMute() }
    @objc private func menuActionEmptyTrash() { SystemActionsManager.emptyTrash() }
    
    // Battery Settings Actions
    
    @objc private func menuActionToggleBatteryAlerts() {
        isBatteryAlertsEnabled.toggle()
    }
    
    @objc private func menuActionSetFullThreshold(_ sender: NSMenuItem) {
        fullBatteryThreshold = sender.tag
    }
    
    @objc private func menuActionSetLowThreshold(_ sender: NSMenuItem) {
        lowBatteryThreshold = sender.tag
    }
    
    // General Settings Actions
    
    @objc private func menuActionToggleAlertSound() { alertSoundEnabled.toggle() }
    
    @objc private func menuActionSetSnoozeLength(_ sender: NSMenuItem) {
        snoozeLengthMinutes = sender.tag
    }
    
    @objc private func menuActionToggleKeepAwakeAlarms() {
        keepAwakeForAlarmsEnabled.toggle()
    }
    
    @objc private func menuActionToggleShortcuts() {
        isShortcutsEnabled.toggle()
    }
    
    @objc private func menuActionToggleLaunchAtLogin() {
        do {
            if SMAppService.mainApp.status == .enabled {
                try SMAppService.mainApp.unregister()
            } else {
                try SMAppService.mainApp.register()
            }
        } catch {
            let alert = NSAlert()
            alert.messageText = "Launch at Login Failed"
            alert.informativeText = "Unable to change Launch at Login setting: \(error.localizedDescription)"
            alert.runModal()
        }
    }
    
    @objc private func menuActionTestAlert() {
        OverlayManager.shared.showOverlay(
            type: .test,
            soundEnabled: alertSoundEnabled,
            isPresentationMode: false,
            primaryTitle: "Dismiss Test",
            onPrimary: {}
        )
    }
    
    @objc private func menuActionQuit() {
        NSApp.terminate(nil)
    }
    
    // MARK: - Claude Accounts Actions & Logic
    
    private func checkClaudeAccounts() {
        let now = Date()
        let calendar = Calendar.current
        var resettingAccounts: [ClaudeAccount] = []
        
        for i in 0..<claudeAccounts.count {
            var account = claudeAccounts[i]
            guard !account.isPaused, let resetDate = account.nextResetDate else { continue }
            
            if let lastFired = account.lastFiredDate {
                if calendar.isDate(lastFired, equalTo: now, toGranularity: .minute) {
                    continue
                }
            }
            
            if resetDate <= now {
                account.lastFiredDate = now
                resettingAccounts.append(account)
                
                var next = resetDate.addingTimeInterval(Double(claudeSessionLengthHours * 3600))
                while next <= now {
                    next = next.addingTimeInterval(Double(claudeSessionLengthHours * 3600))
                }
                
                if account.isAutoRenewEnabled {
                    account.nextResetDate = next
                } else {
                    account.nextResetDate = nil
                }
                claudeAccounts[i] = account
            }
        }
        
        if !resettingAccounts.isEmpty {
            saveClaudeAccounts()
            
            let names = resettingAccounts.map { $0.name }
            let nextResetStr: String
            if let firstNext = resettingAccounts.compactMap({ $0.nextResetDate }).min() {
                let df = DateFormatter()
                if calendar.isDateInToday(firstNext) {
                    df.dateFormat = "h:mm a"
                } else {
                    df.dateFormat = "EEE h:mm a"
                }
                nextResetStr = df.string(from: firstNext)
            } else {
                nextResetStr = "Not scheduled"
            }
            
            let sessionLen = claudeSessionLengthHours
            
            OverlayManager.shared.showOverlay(
                type: .claudeReset(accountNames: names, nextResetStr: nextResetStr, sessionLengthHours: sessionLen),
                soundEnabled: alertSoundEnabled,
                isPresentationMode: isPresentationMode,
                primaryTitle: "Dismiss",
                secondaryTitle: "Stop auto-renew",
                onPrimary: {},
                onSecondary: { [weak self] in
                    guard let self = self else { return }
                    for name in names {
                        if let idx = self.claudeAccounts.firstIndex(where: { $0.name == name }) {
                            self.claudeAccounts[idx].isAutoRenewEnabled = false
                            self.claudeAccounts[idx].nextResetDate = nil
                        }
                    }
                    self.saveClaudeAccounts()
                }
            )
        }
    }
    
    @objc private func menuActionClaudeSetTime(_ sender: NSMenuItem) {
        guard let id = sender.representedObject as? UUID, let idx = claudeAccounts.firstIndex(where: { $0.id == id }) else { return }
        let account = claudeAccounts[idx]
        
        let alert = NSAlert()
        alert.messageText = "Set Next Reset Time"
        alert.informativeText = "Enter the time for \(account.name)'s next Claude limit reset:"
        alert.addButton(withTitle: "Set Time")
        alert.addButton(withTitle: "Cancel")
        
        if let icon = NSImage(systemSymbolName: "sparkles", accessibilityDescription: "Claude") {
            let config = NSImage.SymbolConfiguration(pointSize: 48, weight: .medium)
            alert.icon = icon.withSymbolConfiguration(config)
        }
        
        let container = NSView(frame: NSRect(x: 0, y: 0, width: 330, height: 75))
        
        let timeLabel = NSTextField(labelWithString: "Reset Time:")
        timeLabel.frame = NSRect(x: 0, y: 42, width: 85, height: 20)
        timeLabel.alignment = .right
        
        let timeField = NSTextField(frame: NSRect(x: 92, y: 40, width: 230, height: 24))
        timeField.placeholderString = "e.g. 10:30 AM or 15:30"
        
        let hintLabel = NSTextField(labelWithString: "If the time has passed today, tomorrow's time will be used.")
        hintLabel.frame = NSRect(x: 0, y: 10, width: 330, height: 20)
        hintLabel.textColor = .secondaryLabelColor
        hintLabel.font = NSFont.systemFont(ofSize: 11)
        
        container.addSubview(timeLabel)
        container.addSubview(timeField)
        container.addSubview(hintLabel)
        
        alert.accessoryView = container
        
        DispatchQueue.main.async {
            alert.window.makeFirstResponder(timeField)
        }
        
        if alert.runModal() == .alertFirstButtonReturn {
            let text = timeField.stringValue.trimmingCharacters(in: .whitespacesAndNewlines)
            if let parsedDate = parseTimeString(text) {
                claudeAccounts[idx].nextResetDate = parsedDate
                claudeAccounts[idx].isAutoRenewEnabled = true
                claudeAccounts[idx].isPaused = false
                saveClaudeAccounts()
            } else {
                let errAlert = NSAlert()
                errAlert.messageText = "Invalid Time Format"
                errAlert.informativeText = "Please enter a valid time like '10:30 AM', '10:30', or '15:30'."
                errAlert.alertStyle = .warning
                errAlert.runModal()
            }
        }
    }
    
    private func parseTimeString(_ input: String) -> Date? {
        let trimmed = input.trimmingCharacters(in: .whitespacesAndNewlines)
        if trimmed.isEmpty { return nil }
        
        let formats = ["h:mm a", "h:mma", "HH:mm", "H:mm", "h a", "ha"]
        let calendar = Calendar.current
        let now = Date()
        
        for fmt in formats {
            let df = DateFormatter()
            df.locale = Locale(identifier: "en_US_POSIX")
            df.dateFormat = fmt
            if let parsedDate = df.date(from: trimmed) {
                let parsedComps = calendar.dateComponents([.hour, .minute], from: parsedDate)
                var targetComps = calendar.dateComponents([.year, .month, .day], from: now)
                targetComps.hour = parsedComps.hour
                targetComps.minute = parsedComps.minute
                targetComps.second = 0
                
                if var targetDate = calendar.date(from: targetComps) {
                    if targetDate <= now {
                        targetDate = calendar.date(byAdding: .day, value: 1, to: targetDate) ?? targetDate
                    }
                    return targetDate
                }
            }
        }
        return nil
    }
    
    @objc private func menuActionClaudeResetIn(_ sender: NSMenuItem) {
        guard let obj = sender.representedObject as? ClaudeQuickResetObject,
              let idx = claudeAccounts.firstIndex(where: { $0.id == obj.accountId }) else { return }
        claudeAccounts[idx].nextResetDate = Date().addingTimeInterval(Double(obj.minutes * 60))
        claudeAccounts[idx].isAutoRenewEnabled = true
        claudeAccounts[idx].isPaused = false
        saveClaudeAccounts()
    }
    
    @objc private func menuActionClaudeStartedNow(_ sender: NSMenuItem) {
        guard let id = sender.representedObject as? UUID, let idx = claudeAccounts.firstIndex(where: { $0.id == id }) else { return }
        claudeAccounts[idx].nextResetDate = Date().addingTimeInterval(Double(claudeSessionLengthHours * 3600))
        claudeAccounts[idx].isAutoRenewEnabled = true
        claudeAccounts[idx].isPaused = false
        saveClaudeAccounts()
    }
    
    @objc private func menuActionClaudeTogglePause(_ sender: NSMenuItem) {
        guard let id = sender.representedObject as? UUID, let idx = claudeAccounts.firstIndex(where: { $0.id == id }) else { return }
        claudeAccounts[idx].isPaused.toggle()
        saveClaudeAccounts()
    }
    
    @objc private func menuActionClaudeClear(_ sender: NSMenuItem) {
        guard let id = sender.representedObject as? UUID, let idx = claudeAccounts.firstIndex(where: { $0.id == id }) else { return }
        claudeAccounts[idx].nextResetDate = nil
        saveClaudeAccounts()
    }
    
    @objc private func menuActionClaudeRename(_ sender: NSMenuItem) {
        guard let id = sender.representedObject as? UUID, let idx = claudeAccounts.firstIndex(where: { $0.id == id }) else { return }
        let currentName = claudeAccounts[idx].name
        
        let alert = NSAlert()
        alert.messageText = "Rename Account"
        alert.informativeText = "Enter a new name for \(currentName):"
        alert.addButton(withTitle: "Save")
        alert.addButton(withTitle: "Cancel")
        
        let nameField = NSTextField(frame: NSRect(x: 0, y: 0, width: 260, height: 24))
        nameField.stringValue = currentName
        alert.accessoryView = nameField
        
        DispatchQueue.main.async {
            alert.window.makeFirstResponder(nameField)
        }
        
        if alert.runModal() == .alertFirstButtonReturn {
            let newName = nameField.stringValue.trimmingCharacters(in: .whitespacesAndNewlines)
            if !newName.isEmpty {
                claudeAccounts[idx].name = newName
                saveClaudeAccounts()
            }
        }
    }
    
    @objc private func menuActionClaudeDelete(_ sender: NSMenuItem) {
        guard let id = sender.representedObject as? UUID, let idx = claudeAccounts.firstIndex(where: { $0.id == id }) else { return }
        claudeAccounts.remove(at: idx)
        saveClaudeAccounts()
    }
    
    @objc private func menuActionClaudeAddAccount() {
        let alert = NSAlert()
        alert.messageText = "Add Claude Account"
        alert.informativeText = "Enter account name (e.g. Work, Personal, Client A):"
        alert.addButton(withTitle: "Add Account")
        alert.addButton(withTitle: "Cancel")
        
        let nameField = NSTextField(frame: NSRect(x: 0, y: 0, width: 260, height: 24))
        nameField.placeholderString = "e.g. Work"
        alert.accessoryView = nameField
        
        DispatchQueue.main.async {
            alert.window.makeFirstResponder(nameField)
        }
        
        if alert.runModal() == .alertFirstButtonReturn {
            let rawName = nameField.stringValue.trimmingCharacters(in: .whitespacesAndNewlines)
            let name = rawName.isEmpty ? "Account" : rawName
            let newAcct = ClaudeAccount(name: name)
            claudeAccounts.append(newAcct)
            saveClaudeAccounts()
        }
    }
    
    @objc private func menuActionClaudeSetSessionLength(_ sender: NSMenuItem) {
        claudeSessionLengthHours = sender.tag
    }
}

// MARK: - Main Entry Point

@main
@MainActor
struct FocusAlertApp {
    static func main() {
        let app = NSApplication.shared
        let delegate = AppDelegate()
        app.delegate = delegate
        app.setActivationPolicy(.accessory)
        app.run()
    }
}
