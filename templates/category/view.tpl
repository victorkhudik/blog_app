{extends file="layouts/main.tpl"}

{block name=content}
    <div class="category-page">
        <div class="category-header">
            <h1 class="page-title">{$category.title}</h1>
            {if $category.description}
                <p class="description">{$category.description}</p>
            {/if}
        </div>
    </div>
    {if $sortOptions}
    <div class="sort-controls">
        <label for="sort-select">Сортировка:</label>
        <select id="sort-select" onchange="window.location.search=this.value">
            {foreach $sortOptions as $sort => $optionList}
                {foreach $optionList as $option}
                    <option value="?page=1{if !isset($option.default) || !$option.default}&sort={$sort}&dir={$option.dir}{/if}"
                            {if $selectedSortOptions.sort==$sort && $selectedSortOptions.dir==$option.dir}
                                selected
                            {/if}
                    >
                        {$option.label}
                    </option>
                {/foreach}
            {/foreach}
        </select>
    </div>
    {/if}

    {if $posts > 0}
        <div class="posts-grid">
            {foreach $posts as $post}
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
        {include file="partials/pagination.tpl"
        current_page=$pagination.current_page
        last_page=$pagination.last_page
        base_url=$pagination.base_url
        total=$pagination.total
        per_page=$pagination.per_page
        show_edges=$pagination.show_edges
        max_pages=$pagination.max_pages}
    {else}
        <div class="empty-state">
            <p>В этой категории пока нет статей.</p>
        </div>
    {/if}
{/block}