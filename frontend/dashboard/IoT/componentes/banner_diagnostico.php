<!-- Banner: Diagnóstico Inteligente SIRA -->
<div style="background: linear-gradient(90deg, rgba(16, 185, 129, 0.15) 0%, rgba(6, 78, 59, 0.4) 100%); border: 1px solid rgba(16, 185, 129, 0.4); padding: 1.2rem; margin-bottom: 2rem; border-radius: 12px; display: flex; align-items: center; gap: 1.5rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
    <div style="background: var(--color-primary); width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 0 15px rgba(16, 185, 129, 0.4);">
        <svg width="28" height="28" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
    </div>
    <div>
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
            <h4 style="color: var(--color-primary); margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 800;">SIRA Intelligence | Estado del Cultivo</h4>
            <?php if ($hora_virtual_api && $hora_virtual_api !== '--:--'): ?>
                <span style="background: rgba(16, 185, 129, 0.2); color: var(--color-primary); padding: 2px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; border: 1px solid var(--color-primary); white-space: nowrap;">
                    🕒 HORA VIRTUAL: <?= htmlspecialchars($hora_virtual_api) ?>
                </span>
            <?php endif; ?>
        </div>
        <p style="color: #f8fafc; margin: 0; font-size: 1.1rem; font-weight: 600; line-height: 1.4;">
            <?= htmlspecialchars($diagnostico_humano) ?>
        </p>
    </div>
</div>
