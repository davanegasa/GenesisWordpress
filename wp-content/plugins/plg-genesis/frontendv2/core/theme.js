import { api } from '../api/client.js';

export async function loadTheme() {
    try {
        const r = await api.get('/theme');
        return (r && r.data) || {};
    } catch (e) {
        return {};
    }
}

export function applyTheme(vars) {
    if (!vars) return;
    const root = document.documentElement;
    // Solo sobrescribir claves presentes; respetar tokens por defecto
    Object.entries(vars).forEach(([k, v]) => {
        if (v != null && v !== '') root.style.setProperty(`--plg-${k}`, v);
    });
}

// Paletas predefinidas (presets completos)
export const themePresets = {
    emmausModal: {
        accent: '#0c497a', success: '#3fab49', warning: '#f59e0b', danger: '#e11d48', info: '#3b82f6',
        bg: '#f9fafb', cardBg: '#ffffff', text: '#1e293b', mutedText: '#64748b', border: '#e2e8f0',
        sidebarBg: '#0a1224', sidebarText: '#f1f5f9'
    },
    emmausBlue: {
        accent: '#0c497a', success: '#3fab49', warning: '#fff100', danger: '#e11d48', info: '#3b82f6',
        bg: '#f5f7fb', cardBg: '#ffffff', text: '#0f172a', mutedText: '#6b7280', border: '#d7dfeb'
    },
    emmausGreen: {
        accent: '#3fab49', success: '#3fab49', warning: '#fff100', danger: '#e11d48', info: '#0c497a',
        bg: '#f5fbf7', cardBg: '#ffffff', text: '#0f172a', mutedText: '#5f6b6f', border: '#cfe7d5'
    },
    oceanBlue: {
        accent: '#0b3b8c', success: '#22c55e', warning: '#f59e0b', danger: '#ef4444', info: '#3b82f6',
        bg: '#eef5ff', cardBg: '#ffffff', text: '#0a0f1e', mutedText: '#4b5563', border: '#bfd7ff'
    },
    dark: {
        accent: '#3fab49', success: '#22c55e', warning: '#eab308', danger: '#f87171', info: '#60a5fa',
        bg: '#0b1220', cardBg: '#111827', text: '#f3f4f6', mutedText: '#9ca3af', border: '#334155'
    },
    minimal: {
        accent: '#0c497a', success: '#3fab49', warning: '#eab308', danger: '#ef4444', info: '#3b82f6',
        bg: '#ffffff', cardBg: '#ffffff', text: '#0f172a', mutedText: '#6b7280', border: '#e5e7eb'
    }
};

export function applyPreset(name) {
    const preset = themePresets[name];
    if (!preset) return;
    applyTheme(preset);
}