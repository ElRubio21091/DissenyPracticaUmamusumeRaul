<?php
require __DIR__ . '/includes/helpers.php';

$characters = [
    ['slug' => 'copano-rickey',      'name' => 'Copano Rickey',      'va' => 'Komoni Inagaki'],
    ['slug' => 'matikanefukukitaru', 'name' => 'Matikanefukukitaru', 'va' => 'Hiyori Nitta'],
    ['slug' => 'marvelous-sunday',   'name' => 'Marvelous Sunday',   'va' => 'Marie Miyake'],
    ['slug' => 'grass-wonder',       'name' => 'Grass Wonder',       'va' => 'Rena Maeda'],
    ['slug' => 'el-condor-pasa',     'name' => 'El Condor Pasa',     'va' => 'Minami Takahashi'],
    ['slug' => 'tamamo-cross',       'name' => 'Tamamo Cross',       'va' => 'Naomi Ozora'],
    ['slug' => 'mayano-top-gun',     'name' => 'Mayano Top Gun',     'va' => 'Mio Hoshitani'],
    ['slug' => 'twin-turbo',         'name' => 'Twin Turbo',         'va' => 'Miharu Hanai'],
    ['slug' => 'yamanin-zephyr',     'name' => 'Yamanin Zephyr',     'va' => 'Riona Imaizumi'],
    ['slug' => 'shinko-windy',       'name' => 'Shinko Windy',       'va' => 'Yuuki Takada'],
];

$title = 'Characters · Umamusume: Pretty Derby';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
?>

<main class="mx-auto w-full max-w-7xl flex-1 px-4 pb-8 pt-10 xl:max-w-none xl:px-[8.8vw] xl:pt-[4.75vw]">
    <div class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
        <span class="hidden w-56 sm:block"></span>
        <h1 class="text-[length:clamp(26px,1.878vw,48px)] font-bold leading-none">CHARACTERS</h1>

        <div class="flex w-56 items-center gap-3">
            <label for="q" class="sr-only">Buscar personaje</label>
            <input id="q" type="search" placeholder="Search"
                   class="w-full rounded-full border border-black bg-white px-4 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-pink">
            <button type="button" aria-label="Buscar"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-black bg-white hover:bg-brand-pink/20">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                </svg>
            </button>
        </div>
    </div>

    <h2 class="mt-6 text-[length:clamp(22px,1.565vw,40px)] font-bold leading-none xl:mt-[1.88vw]">UMAMUSUME</h2>

    <ul class="mt-4 grid grid-cols-2 gap-x-6 gap-y-8 sm:grid-cols-3 lg:grid-cols-5 xl:mt-[2.09vw] xl:grid-cols-[repeat(5,9.8vw)] xl:gap-x-[8.8vw] xl:gap-y-[2.14vw]">
        <?php foreach ($characters as $c):
            $img = 'assets/img/characters/' . $c['slug'] . '.png'; ?>
            <li>
                <a href="#" class="mx-auto block w-full max-w-[160px] xl:mx-0 xl:max-w-none">
                    <div class="aspect-[3/4] overflow-hidden border border-black bg-white">
                        <?php if (asset_exists($img)): ?>
                            <img src="<?= $img ?>" alt="<?= $c['name'] ?>" loading="lazy" class="h-full w-full object-cover object-top">
                        <?php else: ?>
                            <div class="flex h-full w-full items-center justify-center bg-gradient-to-b from-pink-100 to-purple-200 text-5xl font-bold text-brand-pink"
                                 role="img" aria-label="<?= $c['name'] ?>"><?= mb_substr($c['name'], 0, 1) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="border border-t-0 border-black bg-brand-pink px-1 py-1 text-center text-white xl:py-[0.15vw]">
                        <p class="truncate text-[length:clamp(11px,0.835vw,18px)] font-bold leading-tight"><?= $c['name'] ?></p>
                        <p class="truncate text-[length:clamp(10px,0.73vw,16px)] font-bold leading-tight">VA: <?= $c['va'] ?></p>
                    </div>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
