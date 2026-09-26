import type { LucideIcon } from 'lucide-react';

type Props = {
	icon: LucideIcon;
	size?: number;
	className?: string;
	fill?: string;
};

/** Lucide SVG icon — shared across admin builder UI. */
export function BuilderIcon( { icon: Icon, size = 16, className, fill }: Props ) {
	return (
		<Icon
			size={ size }
			className={ className }
			strokeWidth={ 1.75 }
			fill={ fill }
			aria-hidden
		/>
	);
}
