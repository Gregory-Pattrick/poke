<?php
function fetchJson(string $url): ?array
{
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_USERAGENT => 'PHP-Pokedex-Test/1.0',
        CURLOPT_SSL_VERIFYPEER => false, // apenas para teste local
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        return null;
    }

    $data = json_decode($response, true);
    return is_array($data) ? $data : null;
}

$search = trim($_GET['pokemon'] ?? '');
$pokemon = null;
$error = null;

if ($search !== '') {
    $query = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $search));
    $pokemon = fetchJson("https://pokeapi.co/api/v2/pokemon/{$query}");

    if (!$pokemon) {
        $error = "Pokémon não encontrado. Tente nomes como pikachu, charizard, bulbasaur ou squirtle.";
    }
}

$defaultPokemons = ['pikachu', 'charizard', 'bulbasaur', 'squirtle', 'mewtwo', 'ho-oh'];
$cards = [];

foreach ($defaultPokemons as $name) {
    $data = fetchJson("https://pokeapi.co/api/v2/pokemon/{$name}");
    if ($data) {
        $cards[] = $data;
    }
}

function typeBadgeClass(string $type): string
{
    return match ($type) {
        'fire' => 'bg-danger',
        'water' => 'bg-primary',
        'grass' => 'bg-success',
        'electric' => 'bg-warning text-dark',
        'psychic' => 'bg-pink',
        'poison' => 'bg-purple',
        'normal' => 'bg-secondary',
        'flying' => 'bg-info text-dark',
        default => 'bg-dark',
    };
}

function pokemonImage(array $pokemon): string
{
    return $pokemon['sprites']['other']['official-artwork']['front_default']
        ?? $pokemon['sprites']['front_default']
        ?? '';
}
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pokédex PHP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 45%, #991b1b 100%);
            color: #fff;
        }

        .hero-card, .pokemon-card, .result-card {
            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
        }

        .pokemon-card {
            transition: transform .2s ease, box-shadow .2s ease;
            height: 100%;
        }

        .pokemon-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
        }

        .pokemon-img {
            max-height: 180px;
            object-fit: contain;
            filter: drop-shadow(0 18px 16px rgba(0, 0, 0, 0.35));
        }

        .result-img {
            max-height: 260px;
            object-fit: contain;
            filter: drop-shadow(0 22px 18px rgba(0, 0, 0, 0.4));
        }

        .stat-bar {
            height: 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            overflow: hidden;
        }

        .stat-fill {
            height: 100%;
            background: #facc15;
            border-radius: 999px;
        }

        .bg-purple { background-color: #7e22ce; }
        .bg-pink { background-color: #db2777; }

        .form-control, .btn {
            border-radius: 14px;
        }

        .text-soft {
            color: rgba(255, 255, 255, 0.78);
        }
    </style>
</head>
<body>
    <main class="container py-5">
        <section class="hero-card p-4 p-md-5 mb-5 text-center">
            <span class="badge bg-warning text-dark mb-3 px-3 py-2">PokéAPI</span>
            <h1 class="display-5 fw-bold mb-3">Pokédex</h1>
            <p class="lead text-soft mb-4">Busque um Pokémon pelo nome ou ID e veja imagem, tipos, habilidades e atributos principais.</p>

            <form class="row g-2 justify-content-center" method="GET" action="index.php">
                <div class="col-12 col-md-7 col-lg-5">
                    <input
                        type="text"
                        name="pokemon"
                        class="form-control form-control-lg"
                        placeholder="Ex: pikachu, charizard ou 25"
                        value="<?= htmlspecialchars($search) ?>"
                    >
                </div>
                <div class="col-12 col-md-auto">
                    <button class="btn btn-warning btn-lg w-100 fw-semibold" type="submit">Buscar Pokémon</button>
                </div>
            </form>
        </section>

        <?php if ($error): ?>
            <div class="alert alert-danger rounded-4 border-0 shadow-sm" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($pokemon): ?>
            <section class="result-card p-4 p-md-5 mb-5">
                <div class="row align-items-center g-4">
                    <div class="col-12 col-md-5 text-center">
                        <img class="img-fluid result-img" src="<?= htmlspecialchars(pokemonImage($pokemon)) ?>" alt="<?= htmlspecialchars($pokemon['name']) ?>">
                    </div>
                    <div class="col-12 col-md-7">
                        <div class="d-flex align-items-center gap-3 mb-2 flex-wrap">
                            <h2 class="text-capitalize fw-bold mb-0"><?= htmlspecialchars($pokemon['name']) ?></h2>
                            <span class="badge bg-light text-dark">#<?= str_pad((string)$pokemon['id'], 3, '0', STR_PAD_LEFT) ?></span>
                        </div>

                        <div class="mb-4">
                            <?php foreach ($pokemon['types'] as $type): ?>
                                <span class="badge <?= typeBadgeClass($type['type']['name']) ?> me-2 px-3 py-2 text-capitalize">
                                    <?= htmlspecialchars($type['type']['name']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6 col-lg-3">
                                <div class="bg-dark bg-opacity-25 rounded-4 p-3">
                                    <small class="text-soft">Altura</small>
                                    <div class="fw-bold"><?= $pokemon['height'] / 10 ?> m</div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3">
                                <div class="bg-dark bg-opacity-25 rounded-4 p-3">
                                    <small class="text-soft">Peso</small>
                                    <div class="fw-bold"><?= $pokemon['weight'] / 10 ?> kg</div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3">
                                <div class="bg-dark bg-opacity-25 rounded-4 p-3">
                                    <small class="text-soft">Base XP</small>
                                    <div class="fw-bold"><?= htmlspecialchars((string)$pokemon['base_experience']) ?></div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3">
                                <div class="bg-dark bg-opacity-25 rounded-4 p-3">
                                    <small class="text-soft">Habilidades</small>
                                    <div class="fw-bold"><?= count($pokemon['abilities']) ?></div>
                                </div>
                            </div>
                        </div>

                        <h5 class="fw-bold mb-3">Status</h5>
                        <?php foreach ($pokemon['stats'] as $stat): ?>
                            <?php $value = (int)$stat['base_stat']; $percent = min(100, ($value / 160) * 100); ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-capitalize"><?= htmlspecialchars(str_replace('-', ' ', $stat['stat']['name'])) ?></span>
                                    <strong><?= $value ?></strong>
                                </div>
                                <div class="stat-bar">
                                    <div class="stat-fill" style="width: <?= $percent ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section>
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                <div>
                    <h3 class="fw-bold mb-1">Sugestões populares</h3>
                    <p class="text-soft mb-0">Clique em um nome para buscar rapidamente.</p>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($cards as $card): ?>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <article class="pokemon-card p-4 text-center">
                            <img class="img-fluid pokemon-img mb-3" src="<?= htmlspecialchars(pokemonImage($card)) ?>" alt="<?= htmlspecialchars($card['name']) ?>">
                            <h4 class="text-capitalize fw-bold mb-2"><?= htmlspecialchars($card['name']) ?></h4>
                            <p class="text-soft mb-3">#<?= str_pad((string)$card['id'], 3, '0', STR_PAD_LEFT) ?></p>
                            <div class="mb-4">
                                <?php foreach ($card['types'] as $type): ?>
                                    <span class="badge <?= typeBadgeClass($type['type']['name']) ?> me-1 text-capitalize">
                                        <?= htmlspecialchars($type['type']['name']) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <a class="btn btn-outline-light w-100" href="?pokemon=<?= urlencode($card['name']) ?>">Ver detalhes</a>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</body>
</html>
