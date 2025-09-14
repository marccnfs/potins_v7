/**
 * Loader animé
 */
import { h, render} from "preact";
export function Loader ({ className = 'icon', ...props }) {
  return <spinning-dots className={className} {...props} />
}
