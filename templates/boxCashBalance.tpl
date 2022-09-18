{if $balance|count}
    <dl class="plain dataList">
        {foreach from=$balance key=key item=amount}
            <dt>{$key}</dt>
            <dd>{$amount|currency}</dd>
        {/foreach}
    </dl>
{else}
    {lang}cash.cash.balance.none{/lang}
{/if}
