{if $claims|count}
    <dl class="plain dataList">
        {foreach from=$claims key=key item=amount}
            <dt>{$key}</dt>
            <dd>{$amount|currency}</dd>
        {/foreach}
    </dl>
{else}
    {lang}cash.claim.none{/lang}
{/if}
