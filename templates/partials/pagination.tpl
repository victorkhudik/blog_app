{if isset($last_page) && $last_page > 1}
    <nav class="pagination" aria-label="Пагинация">
        <ul class="pagination-list">
            {if $current_page > 1}
                <li class="pagination-item pagination-prev">
                    <a href="{$base_url}page={$current_page-1}" class="pagination-link" aria-label="Предыдущая страница">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                        Назад
                    </a>
                </li>
            {else}
                <li class="pagination-item pagination-prev disabled">
                    <span class="pagination-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                        Назад
                    </span>
                </li>
            {/if}

            <div class="pagination-pages">
                {if isset($show_edges) && $show_edges}
                    {if $current_page > 2}
                        <li class="pagination-item">
                            <a href="{$base_url}page=1" class="pagination-link">1</a>
                        </li>
                        {if $current_page > 3}
                            <li class="pagination-item pagination-dots">
                                <span class="pagination-link">…</span>
                            </li>
                        {/if}
                    {/if}
                {/if}

                {assign var="max_pages" value=$max_pages|default:5}
                {assign var="half" value=($max_pages - 1) / 2}
                {assign var="half" value=$half|round}

                {assign var="start" value=$current_page - $half}
                {assign var="end" value=$current_page + $half}

                {if $start < 1}
                    {assign var="end" value=$end + (1 - $start)}
                    {assign var="start" value=1}
                {/if}

                {if $end > $last_page}
                    {assign var="start" value=$start - ($end - $last_page)}
                    {assign var="end" value=$last_page}
                {/if}

                {if $start < 1}
                    {assign var="start" value=1}
                {/if}

                {for $i=$start to $end}
                    {if $i == $current_page}
                        <li class="pagination-item active">
                            <span class="pagination-link pagination-current">{$i}</span>
                        </li>
                    {else}
                        <li class="pagination-item">
                            <a href="{$base_url}page={$i}" class="pagination-link">{$i}</a>
                        </li>
                    {/if}
                {/for}

                {if isset($show_edges) && $show_edges}
                    {if $current_page < $last_page - 1}
                        {if $current_page < $last_page - 2}
                            <li class="pagination-item pagination-dots">
                                <span class="pagination-link">…</span>
                            </li>
                        {/if}
                        <li class="pagination-item">
                            <a href="{$base_url}page={$last_page}" class="pagination-link">{$last_page}</a>
                        </li>
                    {/if}
                {/if}
            </div>

            {if $current_page < $last_page}
                <li class="pagination-item pagination-next">
                    <a href="{$base_url}page={$current_page+1}" class="pagination-link" aria-label="Следующая страница">
                        Вперед
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </a>
                </li>
            {else}
                <li class="pagination-item pagination-next disabled">
                    <span class="pagination-link">
                        Вперед
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </span>
                </li>
            {/if}
        </ul>

        {if isset($total) && isset($per_page)}
            <div class="pagination-info">
                {assign var="start_item" value=(($current_page-1) * $per_page) + 1}
                {assign var="end_item" value=$start_item + $per_page - 1}
                {if $end_item > $total}
                    {assign var="end_item" value=$total}
                {/if}
                Показано <strong>{$start_item}</strong> — <strong>{$end_item}</strong> из <strong>{$total}</strong>
            </div>
        {/if}
    </nav>
{/if}