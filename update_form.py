import sys
import re

file_path = r"c:\Users\humeyra.cimen\Desktop\yunusEmre\laraveldynamicqr\resources\views\qr\form.blade.php"

with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

new_css = """@push('styles')
    <style>
        .qr-form-shell {
            width: min(100%, 45rem);
            margin-inline: auto;
        }

        .qr-form-breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--eyebrow-color);
            margin-bottom: 1.25rem;
        }

        .qr-form-breadcrumb-current {
            color: var(--accent-text);
        }

        .apple-glass-panel {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.65), rgba(255, 255, 255, 0.45));
            backdrop-filter: blur(32px);
            -webkit-backdrop-filter: blur(32px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 2.2rem;
            box-shadow: 0 32px 64px rgba(31, 47, 50, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.9);
            overflow: hidden;
        }

        html.dark .apple-glass-panel {
            background: linear-gradient(135deg, rgba(20, 30, 35, 0.65), rgba(10, 15, 20, 0.45));
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 32px 64px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        .qr-form-header {
            padding: 2rem 2.2rem 1.75rem;
            position: relative;
        }

        .qr-form-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 2.2rem;
            right: 2.2rem;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(16, 32, 42, 0.08), transparent);
        }

        html.dark .qr-form-header::after {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.08), transparent);
        }

        .qr-form-header-row {
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
        }

        .qr-form-icon {
            display: inline-flex;
            height: 3.5rem;
            width: 3.5rem;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            border-radius: 1.1rem;
            background: linear-gradient(135deg, rgba(49, 192, 207, 1) 0%, rgba(28, 171, 187, 1) 100%);
            color: white;
            box-shadow: 0 16px 32px rgba(18, 188, 200, 0.25), inset 0 2px 0 rgba(255, 255, 255, 0.3);
        }

        .qr-form-icon svg {
            height: 1.5rem;
            width: 1.5rem;
        }

        .qr-form-title {
            font-size: clamp(1.6rem, 2.5vw, 2.2rem);
            line-height: 1.1;
            letter-spacing: -0.05em;
        }

        .qr-form-copy {
            max-width: 30rem;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-top: 0.5rem;
        }

        .qr-form-body {
            padding: 1.75rem 2.2rem 2rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.3), transparent);
        }

        html.dark .qr-form-body {
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.1), transparent);
        }

        .qr-form-stack {
            display: grid;
            gap: 1.5rem;
        }

        .qr-form-field-label {
            display: block;
            margin-bottom: 0.6rem;
            font-size: 0.85rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            color: var(--brand-ink);
        }

        html.dark .qr-form-field-label {
            color: rgba(231, 243, 244, 0.9);
        }

        .qr-form-input {
            width: 100%;
            min-height: 3.6rem;
            border-radius: 1.2rem;
            padding-inline: 1.25rem;
            font-size: 0.95rem;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(16, 32, 42, 0.08);
            box-shadow: 0 4px 12px rgba(16, 32, 42, 0.02), inset 0 2px 4px rgba(16, 32, 42, 0.02);
            color: var(--brand-ink);
            transition: all 250ms ease;
        }

        html.dark .qr-form-input {
            background: rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2), inset 0 2px 4px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .qr-form-input:focus {
            outline: none;
            background: white;
            border-color: rgba(18, 188, 200, 0.5);
            box-shadow: 0 0 0 3px rgba(18, 188, 200, 0.15), 0 8px 24px rgba(18, 188, 200, 0.1);
        }

        html.dark .qr-form-input:focus {
            background: rgba(0, 0, 0, 0.4);
            border-color: rgba(18, 188, 200, 0.5);
            box-shadow: 0 0 0 3px rgba(18, 188, 200, 0.25), 0 8px 24px rgba(0, 0, 0, 0.4);
        }

        .qr-form-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding-top: 1.5rem;
            margin-top: 1rem;
            border-top: 1px solid rgba(16, 32, 42, 0.06);
        }

        html.dark .qr-form-actions {
            border-top-color: rgba(255, 255, 255, 0.06);
        }

        .qr-form-submit {
            min-height: 3.2rem;
            flex: 1 1 auto;
            font-size: 0.95rem;
            border-radius: 1rem;
            box-shadow: 0 12px 24px rgba(18, 188, 200, 0.25);
        }

        .qr-form-cancel {
            min-height: 3.2rem;
            min-width: 7rem;
            padding-inline: 1.5rem;
            font-size: 0.95rem;
            border-radius: 1rem;
        }

        .qr-form-meta {
            display: grid;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .qr-form-meta-card {
            border-radius: 1.2rem;
            padding: 1rem 1.25rem;
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 8px 16px rgba(16, 32, 42, 0.03);
            backdrop-filter: blur(12px);
        }

        html.dark .qr-form-meta-card {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .qr-form-status {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            cursor: pointer;
            transition: all 200ms ease;
        }

        .qr-form-status:hover {
            background: rgba(255, 255, 255, 0.8);
        }

        html.dark .qr-form-status:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        /* Apple style toggle switch */
        .qr-form-status-toggle {
            appearance: none;
            width: 3.2rem;
            height: 1.8rem;
            background: rgba(16, 32, 42, 0.2);
            border-radius: 999px;
            position: relative;
            cursor: pointer;
            outline: none;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
            transition: background 300ms ease;
            margin: 0;
        }

        html.dark .qr-form-status-toggle {
            background: rgba(255, 255, 255, 0.15);
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);
        }

        .qr-form-status-toggle::after {
            content: '';
            position: absolute;
            top: 0.15rem;
            left: 0.15rem;
            width: 1.5rem;
            height: 1.5rem;
            background: white;
            border-radius: 50%;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transition: transform 300ms cubic-bezier(0.4, 0.0, 0.2, 1);
        }

        .qr-form-status-toggle:checked {
            background: #12bcc8;
        }

        .qr-form-status-toggle:checked::after {
            transform: translateX(1.4rem);
        }

        .qr-form-tools {
            margin-top: 1.5rem;
            background: rgba(255, 255, 255, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(16px);
            border-radius: 1.2rem;
            padding: 1rem 1.25rem;
        }

        html.dark .qr-form-tools {
            background: rgba(255, 255, 255, 0.02);
            border-color: rgba(255, 255, 255, 0.05);
        }

        @media (min-width: 48rem) {
            .qr-form-stack {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .qr-form-meta {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 64rem) {
            .qr-form-page {
                min-height: calc(100svh - 8.25rem);
                align-items: center;
            }
        }

        @media (max-width: 63.999rem) {
            .qr-form-header,
            .qr-form-body {
                padding-inline: 1.5rem;
            }

            .qr-form-header {
                padding-top: 1.5rem;
                padding-bottom: 1.25rem;
            }

            .qr-form-body {
                padding-top: 1.25rem;
                padding-bottom: 1.5rem;
            }
            
            .qr-form-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .qr-form-cancel {
                width: 100%;
            }
        }
    </style>
@endpush"""

content = re.sub(r"@push\('styles'\).*?@endpush", new_css, content, flags=re.DOTALL)

# other html changes
content = content.replace("qr-form-card page-card apple-glass-heavy", "qr-form-card apple-glass-panel")
content = content.replace("qr-form-status-toggle h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500", "qr-form-status-toggle")


with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)
print("Updated successfully")
