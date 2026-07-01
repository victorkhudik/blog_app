<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$page_title|default:$site_name}</title>
    <link rel="stylesheet" href="{$base_url}web/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header class="header">
    <div class="container">
        <div class="header-content">
            <a href="{$base_url}" class="logo">{$site_name}</a>
        </div>
    </div>
</header>

<main class="main">
    <div class="container">
        {block name=content}{/block}
    </div>
</main>

<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <p>&copy; {$smarty.now|date_format:"%Y"} {$site_name}. Все права защищены.</p>
        </div>
    </div>
</footer>
</body>
</html>