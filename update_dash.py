import sys
import re

file_path = r"c:\Users\humeyra.cimen\Desktop\yunusEmre\laraveldynamicqr\resources\views\dashboard\index.blade.php"

with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

new_css = """@push('styles')
    <style>
        .dashboard-shell {
            width: min(100%, 82rem);
            max-width: none;
            min-height: calc(100svh - 6.2rem);
        }

        .dashboard-top {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        @media (min-width: 48rem) {
            .dashboard-top {
                flex-direction: row;
                align-items: flex-end;
                justify-content: space-between;
            }
        }

        .dashboard-title-area {
            max-width: 38rem;
        }

        .dashboard-department {
            color: var(--accent-text);
        }

        .dashboard-action-rail {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.8rem;
        }

        /* Glass Cards */
        .apple-glass-panel {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.55), rgba(255, 255, 255, 0.35));
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid rgba(255, 255, 255, 0.65);
            border-radius: 1.8rem;
            box-shadow: 0 24px 54px rgba(31, 47, 50, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        html.dark .apple-glass-panel {
            background: linear-gradient(135deg, rgba(20, 30, 35, 0.65), rgba(10, 15, 20, 0.45));
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 24px 54px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        /* Metrics */
        .dashboard-metrics {
            display: grid;
            gap: 1.25rem;
            grid-template-columns: repeat(auto-fit, minmax(18rem, 1fr));
            margin-bottom: 1.75rem;
        }

        .dashboard-metric {
            position: relative;
            overflow: hidden;
            border-radius: 1.6rem;
            padding: 1.45rem 1.6rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.75), rgba(255, 255, 255, 0.55));
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 16px 36px rgba(18, 188, 200, 0.07), inset 0 1px 0 rgba(255, 255, 255, 0.6);
            transition: all 400ms cubic-bezier(0.2, 0.8, 0.2, 1);
            display: flex;
            flex-direction: column;
            gap: 1rem;
            text-decoration: none;
            color: inherit;
        }

        html.dark .dashboard-metric {
            background: linear-gradient(180deg, rgba(30, 42, 48, 0.55), rgba(15, 22, 26, 0.35));
            border-color: rgba(255, 255, 255, 0.06);
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.03);
        }

        .dashboard-metric:hover {
            transform: translateY(-4px) scale(1.01);
            border-color: rgba(18, 188, 200, 0.35);
            box-shadow: 0 24px 54px rgba(18, 188, 200, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        html.dark .dashboard-metric:hover {
            border-color: rgba(18, 188, 200, 0.25);
            box-shadow: 0 24px 54px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }

        .dashboard-metric.is-active {
            border-color: rgba(18, 188, 200, 0.45);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.95), rgba(240, 252, 253, 0.8));
            box-shadow: 0 0 0 1px rgba(18, 188, 200, 0.25), 0 20px 48px rgba(18, 188, 200, 0.12);
        }

        html.dark .dashboard-metric.is-active {
            border-color: rgba(18, 188, 200, 0.45);
            background: linear-gradient(180deg, rgba(18, 188, 200, 0.18), rgba(18, 188, 200, 0.08));
            box-shadow: 0 0 0 1px rgba(18, 188, 200, 0.35), 0 20px 48px rgba(0, 0, 0, 0.4);
        }

        .dashboard-metric-icon {
            display: inline-flex;
            height: 3.2rem;
            width: 3.2rem;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(18, 188, 200, 0.15), rgba(90, 218, 221, 0.05));
            color: #0f8d97;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5);
            transition: transform 300ms ease;
        }

        html.dark .dashboard-metric-icon {
            background: linear-gradient(135deg, rgba(18, 188, 200, 0.25), rgba(90, 218, 221, 0.05));
            color: #8be8ec;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .dashboard-metric:hover .dashboard-metric-icon {
            transform: scale(1.08) rotate(-4deg);
        }
        
        .dashboard-metric-value {
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -0.04em;
            color: var(--brand-ink);
            line-height: 1.1;
            margin-top: 0.25rem;
        }

        html.dark .dashboard-metric-value {
            color: white;
        }

        .dashboard-metric-copy {
            font-size: 0.84rem;
            line-height: 1.6;
            color: rgba(16, 32, 42, 0.6);
            margin-top: 0.25rem;
        }

        html.dark .dashboard-metric-copy {
            color: rgba(231, 243, 244, 0.6);
        }

        .dashboard-metric-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: auto;
            border-top: 1px solid rgba(16, 32, 42, 0.06);
            padding-top: 1rem;
        }

        html.dark .dashboard-metric-foot {
            border-top-color: rgba(255, 255, 255, 0.06);
        }

        .dashboard-metric-state {
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--eyebrow-color);
        }

        .dashboard-metric-arrow {
            display: inline-flex;
            height: 2.15rem;
            width: 2.15rem;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(16, 32, 42, 0.04);
            color: var(--brand-ink);
            transition: all 300ms cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        html.dark .dashboard-metric-arrow {
            background: rgba(255, 255, 255, 0.08);
            color: white;
        }

        .dashboard-metric:hover .dashboard-metric-arrow {
            background: rgba(18, 188, 200, 0.15);
            color: #0f8d97;
            transform: translateX(6px);
        }

        html.dark .dashboard-metric:hover .dashboard-metric-arrow {
            background: rgba(18, 188, 200, 0.25);
            color: #8be8ec;
        }

        /* List Card */
        .dashboard-list-card {
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
            flex: 1;
            overflow: hidden;
        }

        .dashboard-list-hero {
            padding-bottom: 1.25rem;
            border-bottom: 1px solid rgba(16, 32, 42, 0.06);
            margin-bottom: 0.75rem;
        }

        html.dark .dashboard-list-hero {
            border-bottom-color: rgba(255, 255, 255, 0.06);
        }

        .dashboard-head-copy {
            margin-top: 0.5rem;
            font-size: 0.88rem;
            color: rgba(16, 32, 42, 0.6);
            max-width: 40rem;
            line-height: 1.6;
        }

        html.dark .dashboard-head-copy {
            color: rgba(231, 243, 244, 0.6);
        }

        /* Table */
        .dashboard-table-head {
            display: none;
            padding: 0 1.25rem 0.85rem;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(16, 32, 42, 0.45);
        }

        html.dark .dashboard-table-head {
            color: rgba(231, 243, 244, 0.45);
        }

        .dashboard-table-head span {
            display: block;
        }
        
        .dashboard-list-scroll {
            min-height: 0;
            flex: 1 1 auto;
            overflow: auto;
            padding-right: 0.5rem;
        }
        
        .dashboard-row {
            padding: 1.15rem 1.25rem;
            border-radius: 1.25rem;
            transition: all 250ms cubic-bezier(0.2, 0.8, 0.2, 1);
            background: transparent;
            align-items: center;
            border: 1px solid transparent;
            margin-bottom: 0.45rem;
            position: relative;
        }

        .dashboard-row:hover {
            background: rgba(255, 255, 255, 0.6);
            border-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 12px 32px rgba(18, 188, 200, 0.05);
            transform: scale(1.008);
            z-index: 2;
        }

        html.dark .dashboard-row:hover {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
        }

        .dashboard-row-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--brand-ink);
            letter-spacing: -0.01em;
        }

        html.dark .dashboard-row-title {
            color: white;
        }

        .dashboard-row-subtitle {
            margin-top: 0.45rem;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.55rem;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(16, 32, 42, 0.5);
        }

        html.dark .dashboard-row-subtitle {
            color: rgba(231, 243, 244, 0.5);
        }

        .dashboard-link-code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.3rem;
            border-radius: 999px;
            border: 1px solid rgba(18, 188, 200, 0.2);
            background: rgba(18, 188, 200, 0.08);
            padding: 0.4rem 1rem;
            font-size: 0.84rem;
            font-weight: 800;
            color: #0c757d;
            letter-spacing: 0.05em;
        }

        html.dark .dashboard-link-code {
            background: rgba(18, 188, 200, 0.15);
            color: #8be8ec;
            border-color: rgba(18, 188, 200, 0.3);
        }

        .dashboard-scan-pill {
            display: inline-flex;
            min-width: 4.8rem;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid rgba(16, 32, 42, 0.1);
            background: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 0.9rem;
            font-size: 0.94rem;
            font-weight: 800;
            color: var(--brand-ink);
            box-shadow: 0 4px 16px rgba(16, 32, 42, 0.03);
        }

        html.dark .dashboard-scan-pill {
            border-color: rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.06);
            color: white;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }

        .dashboard-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 2.6rem;
            width: 2.6rem;
            border-radius: 0.85rem;
            border: 1px solid rgba(16, 32, 42, 0.08);
            background: rgba(255, 255, 255, 0.6);
            color: rgba(16, 32, 42, 0.7);
            transition: all 250ms ease;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            cursor: pointer;
        }

        html.dark .dashboard-action-btn {
            border-color: rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.04);
            color: rgba(231, 243, 244, 0.7);
        }

        .dashboard-action-btn:hover {
            transform: translateY(-3px) scale(1.05);
            border-color: rgba(18, 188, 200, 0.35);
            background: white;
            color: #0c757d;
            box-shadow: 0 12px 24px rgba(18, 188, 200, 0.15);
        }

        html.dark .dashboard-action-btn:hover {
            background: rgba(18, 188, 200, 0.2);
            color: #8be8ec;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.35);
        }

        .dashboard-action-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
        }

        @media (min-width: 64rem) {
            .dashboard-table-head {
                display: grid;
                grid-template-columns: minmax(0, 1.4fr) 9rem minmax(0, 1.15fr) 7rem auto;
                gap: 1.5rem;
            }

            .dashboard-row {
                display: grid;
                grid-template-columns: minmax(0, 1.4fr) 9rem minmax(0, 1.15fr) 7rem auto;
                gap: 1.5rem;
            }

            .dashboard-action-grid {
                justify-content: flex-end;
                flex-wrap: nowrap;
            }
        }
    </style>
@endpush"""

content = re.sub(r"@push\('styles'\).*?@endpush", new_css, content, flags=re.DOTALL)

# other html changes
content = content.replace("dashboard-list-card page-card apple-glass-heavy", "dashboard-list-card apple-glass-panel page-card")
content = content.replace("text-[1.6rem]", "text-[1.8rem]")
content = content.replace("md:text-[1.95rem]", "md:text-[2.2rem]")
content = content.replace("px-4.5 py-2.5 text-[0.84rem]", "px-5 py-3 text-[0.9rem] shadow-lg shadow-cyan-500/20")
content = content.replace("dashboard-metric dashboard-metric-link", "dashboard-metric") # cleaner
content = content.replace("h-[2.125rem] w-[2.125rem]", "") # we use dashboard-action-btn styles instead
content = content.replace("h-[1.125rem] w-[1.125rem]", "h-4 w-4")
content = content.replace("surface-chip inline-flex", "surface-chip inline-flex text-cyan-600 bg-cyan-500/10 dark:text-cyan-400")

# modal
content = content.replace("glass-card", "apple-glass-panel")
content = content.replace("bg-black/35", "bg-black/40 backdrop-blur-md")
content = content.replace("bg-white/60", "bg-white/50")


with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)
print("Updated successfully")
