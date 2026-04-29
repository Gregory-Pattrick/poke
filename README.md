# Pokédex PHP + Bootstrap

Projeto simples e responsivo consumindo a [PokéAPI](https://pokeapi.co/) usando apenas PHP, HTML, CSS e Bootstrap via CDN.

## Funcionalidades

- Busca de Pokémon por nome ou ID
- Exibição de imagem oficial
- Tipos, altura, peso, base XP e quantidade de habilidades
- Barras de status
- Cards com sugestões populares
- Layout responsivo com Bootstrap

## Requisitos

- PHP 8+
- Internet ativa para consumir a PokéAPI e carregar o Bootstrap via CDN

## Como rodar localmente

No terminal, dentro da pasta do projeto:

```bash
php -S localhost:8000
```

Depois acesse:

```text
http://localhost:8000
```

## Exemplos de busca

- pikachu
- charizard
- bulbasaur
- squirtle
- 25
- 150

## Estrutura

```text
pokedex_php_bootstrap/
├── index.php
└── README.md
```

## Observação

O projeto não usa Laravel, React, Vue, jQuery ou qualquer framework adicional. Apenas PHP puro, HTML, CSS e Bootstrap.
