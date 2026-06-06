<script>
  export let meta = {};

  import { page } from "@inertiajs/svelte";

  // Defaults gerais
  const defaultTitle = 'Rede Akiba - O Paraíso dos Otakus! | Sua Melhor Fonte de Animes (e Mangás) no Brasil!';
  const defaultDescription = 'Rede Akiba é seu destino definitivo para animes, mangás, doramas e cultura otaku no Brasil.';
  const defaultKeywords = 'animes, mangás, otaku, cultura japonesa, doramas, rede akiba';
  const defaultRobots = 'index, follow';
  const defaultThemeColor = '#0091ff';
  const defaultAppleIcon = '/img/pwa/icon.jpg';
  const defaultFavicon = '/favicon.ico';
  const defaultOgType = 'website';
  const defaultTwitterCreator = '@RedeAkiba';

  $: fullTitle = meta.title ? `${meta.title} | Rede Akiba` : defaultTitle;
  $: metaDescription = meta.description || defaultDescription;
  $: metaKeywords = meta.keywords || defaultKeywords;
  $: metaRobots = meta.robots || defaultRobots;
  $: themeColor = meta.themeColor || defaultThemeColor;
  $: appleIcon = meta.appleIcon || defaultAppleIcon;
  $: favicon = meta.favicon || defaultFavicon;
  $: ogType = meta.ogType || defaultOgType;
  $: twitterCreator = meta.twitterCreator || defaultTwitterCreator;
</script>

<svelte:head>
  <title>
      {fullTitle}
  </title>

  <!-- Meta padrão -->
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content={$page.props.csrf_token}>
  <meta name="description" content={metaDescription} />
  <meta name="keywords" content={metaKeywords} />
  <meta name="author" content={meta.author || 'Rede Akiba'} />
  <meta name="theme-color" content={themeColor} />
  <meta name="language" content="pt-BR" />
  <meta http-equiv="content-language" content="pt-br, en-us" />
  <meta name="robots" content={metaRobots} />

  <!-- Favicons e PWA icons -->
  <link
      rel="icon"
      href={favicon}
      type="image/x-icon"
  />
  <link rel="apple-touch-icon" href={appleIcon} />
  <link rel="manifest" href="/manifest.json" />

  <!-- Canonical -->
  {#if meta.canonical}
    <link rel="canonical" href={meta.canonical} />
    <meta property="og:url" content={meta.canonical} />
  {/if}

  <!-- Open Graph -->
  <meta property="og:title" content={fullTitle} />
  <meta property="og:description" content={metaDescription} />
  <meta property="og:type" content={ogType} />
  <meta property="og:locale" content="pt_BR" />
  {#if meta.ogImage}
    <meta property="og:image" content={meta.ogImage} />
  {/if}

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content={fullTitle} />
  <meta name="twitter:description" content={metaDescription} />
  {#if meta.ogImage}
    <meta name="twitter:image" content={meta.ogImage} />
  {/if}
  <meta name="twitter:creator" content={twitterCreator} />
</svelte:head>
