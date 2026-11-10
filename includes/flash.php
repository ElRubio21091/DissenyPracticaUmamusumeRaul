<?php
/**
 * Autor: Raul Rubio - Pt04
 * flash.php
 * Muestra (una sola vez) el mensaje guardado en sesión por los controladores.
 */
$flash = tomarFlash();
if ($flash):
    $color = $flash['tipo'] === 'ok' ? 'bg-green-100 text-green-800 border-green-600' : 'bg-red-100 text-red-800 border-red-600';
?>
<div id="flash" role="status"
     class="fixed right-4 top-20 z-50 max-w-sm rounded-2xl border px-5 py-3 text-sm font-bold shadow-lg <?= $color ?>">
    <?= e($flash['texto']) ?>
</div>
<script>setTimeout(function () { var f = document.getElementById('flash'); if (f) f.remove(); }, 5000);</script>
<?php endif; ?>
