# FocusAlert

**FocusAlert** is a lightweight macOS menu bar utility app designed to deliver unmissable, full-screen overlay alerts for task timers, alarms, and battery status notifications. It also provides powerful one-click Quick Actions with global keyboard shortcuts to manage your workspace and keep you focused.

---

## Key Features

- **Menu Bar Only (`LSUIElement`)**: Runs unobtrusively in the menu bar with no Dock icon or standard windows.
- **Full-Screen Overlay Alerts**: High-visibility borderless alerts displayed across **all screens** (level `.screenSaver`), appearing over full-screen apps and spaces.
- **Task Countdown Timers**:
  - Custom task name and duration (popups with full Cut/Copy/Paste/Select All support).
  - Quick Timer presets (5, 10, 15, 25, 30, 45, 60 min).
  - Multi-timer support with live countdown displayed next to the menu icon in monospaced digits (e.g. `12:34 +2`).
  - Full-screen red overlay with looping sound when time is up, plus "Snooze 5 min" and "Done" options.
- **Alarms**:
  - Clock time alarms with customizable labels and repeat schedules (Once, Every day, Weekdays, Custom days Mon–Sun).
  - Full-screen blue overlay displaying the current time, alarm label, looping sound, and customizable snooze length.
  - Missed alarm handling on wake from sleep (< 5 min).
  - Optional "Keep Mac Awake for Alarms" system sleep assertion.
- **Claude Limit Reminders**:
  - Track 5-hour usage limit resets for multiple Claude accounts (e.g. "Personal", "Work", "Client A").
  - Shows a PURPLE full-screen overlay alert at reset time ("Claude ready: Work").
  - Auto-renews next reset 5 hours later (or configurable 1–8 hours session length).
  - Supports quick reset options (30m, 1h, 2h, 3h, 4h, 5h), "Session started now", Pause/Resume, Clear, Rename, and Delete.
- **Battery Alerts**:
  - Real-time battery monitoring via IOKit.
  - Green full-charge overlay alert ("Unplug charger") at configurable threshold (80%–100%).
  - Orange low-battery overlay alert ("Plug in charger") at configurable threshold (10%–30%).
- **Quick Actions & Presentation Mode**:
  - **App Control**: Quit All Apps, Quit All Except Current, Force Quit All (with warning dialog), Hide All Apps.
  - **Screen & System**: Lock Screen (native dynamic call), Sleep Display (`pmset`), Keep Awake (30m, 1h, 2h, Indefinitely), Toggle Dark Mode, Hide Desktop Icons, Mute/Unmute, Empty Trash.
  - **Presentation Mode**: One-click toggle that hides desktop icons, keeps Mac awake indefinitely, mutes audio, and pauses battery alerts—with full state restoration when turned off.
- **Global Keyboard Shortcuts**: Carbon-based global hotkeys requiring no Accessibility permission.

---

## Global Keyboard Shortcuts

| Shortcut | Action |
|---|---|
| `⌃⌥T` (`Ctrl + Option + T`) | Add Task Timer |
| `⌃⌥A` (`Ctrl + Option + A`) | Add Alarm |
| `⌃⌥Q` (`Ctrl + Option + Q`) | Quit All Apps |
| `⌃⌥H` (`Ctrl + Option + H`) | Hide All Apps |
| `⌃⌥L` (`Ctrl + Option + L`) | Lock Screen |
| `⌃⌥D` (`Ctrl + Option + D`) | Sleep Display |
| `⌃⌥P` (`Ctrl + Option + P`) | Toggle Presentation Mode |

*Note: Global keyboard shortcuts can be toggled on or off under **Settings ▸ Keyboard Shortcuts**.*

---

## Requirements

- **macOS 13.0 (Ventura)** or later.
- Compatible with both **Apple Silicon** and **Intel** Macs.
- Requires only **Xcode Command Line Tools** (no full Xcode installation required).

---

## Installation & Build Instructions

1. **Install Xcode Command Line Tools** (if not already installed):
   ```bash
   xcode-select --install
   ```

2. **Build the Application**:
   Run the included `build.sh` script in your terminal:
   ```bash
   ./build.sh
   ```
   This will compile `FocusAlert.swift` using `swiftc`, bundle `FocusAlert.app` with `Info.plist`, and apply an ad-hoc code signature.

3. **Install FocusAlert**:
   Move `FocusAlert.app` into your `/Applications` directory:
   ```bash
   mv FocusAlert.app /Applications/
   ```

4. **Launch FocusAlert**:
   Open `FocusAlert.app` from `/Applications` or via Spotlight. FocusAlert will appear as a bell/timer icon in your menu bar.

5. **Enable Launch at Login**:
   Click the FocusAlert menu bar icon, navigate to **Settings**, and toggle **Launch at Login**.

---

## Permissions Setup

FocusAlert uses macOS AppleScript Automation for system toggles (Toggle Dark Mode, Mute/Unmute, and Empty Trash).

When triggering these actions for the first time, macOS will prompt for permission. If permission is denied or missing, FocusAlert will display an alert guiding you:

1. Open **System Settings**.
2. Go to **Privacy & Security** → **Automation**.
3. Under **FocusAlert**, enable permission for **System Events** and **Finder**.

---

## Code Architecture

- **`FocusAlert.swift`**: Single self-contained Swift file containing all models, AppKit menu controllers, SwiftUI full-screen overlay views, IOKit power source readers, Carbon hotkey handlers, and system actions.
- **`build.sh`**: Standalone bash script using `swiftc` and `codesign` to build the `.app` bundle.
