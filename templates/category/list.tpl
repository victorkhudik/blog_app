{extends file="layouts/main.tpl"}

{block name=content}
    <div class="home-page">
        <h1 class="page-title">Список категорий</h1>

        {if $categories}
            {foreach $categories as $item}
                <section class="category-section">
                    <div class="category-header">
                        <div class="category-header-left">
                            <h2 class="category-title">
                                {$item.title}
                            </h2>
                        </div>
                    </div>

                    <div class="posts-grid">
                        {foreach $item.posts as $post}
                            <article class="post-card">
                                {if $post.list_image}
                                    <div class="post-card-image">
                                        <img src="{$base_url}{$post.list_image}" alt="{$post.title}" loading="lazy" width="400"
                                             height="200" />
                                    </div>
                                {/if}
                                <div class="post-card-content">
                                    <h3 class="post-card-title">
                                        <a href="{$base_url}post/{$post.alias}">{$post.title}</a>
                                    </h3>
                                    <p class="post-card-excerpt">
                                        {$post.description|truncate:120:"...":true}
                                    </p>
                                </div>
                            </article>
                        {/foreach}
                    </div>
                    <a href="{$base_url}category/{$item.alias}">
                        <span>Все статьи</span>
                    </a>
                </section>
            {/foreach}
        {else}
            <div class="empty-state">
                <p>Нет опубликованных статей.</p>
            </div>
        {/if}
    </div>
{/block}