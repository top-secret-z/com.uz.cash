{capture assign='pageTitle'}{lang}cash.claim.myClaims{/lang}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='contentTitle'}{lang}cash.claim.myClaims{/lang} <span class="badge">{#$items}</span>{/capture}

{capture assign='sidebarRight'}
    <section class="box">
        <h2 class="boxTitle">{lang}cash.cash.user.balance{/lang}</h2>

        <div class="boxContent">
            {if $userBalance|count}
                <dl class="plain dataList">
                    {foreach from=$userBalance key=key item=amount}
                        <dt>{$key}</dt>
                        <dd>{$amount|currency}</dd>
                    {/foreach}
                </dl>
            {else}
                <p>{lang}cash.cash.user.balance.none{/lang}</p>
            {/if}
        </div>
    </section>

    {event name='boxes'}
{/capture}

{capture assign='contentInteractionPagination'}
    {pages print=true assign=pagesLinks controller="MyClaimList" application="cash" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
{/capture}

{include file='header'}

{if $objects|count}
    <div class="section tabularBox">
        <table class="table">
            <thead>
                <tr>
                    <th class="columnID columnClaimID{if $sortField == 'userClaimID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link application='cash' controller='MyClaimList'}pageNo={@$pageNo}&sortField=userClaimID&sortOrder={if $sortField == 'userClaimID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
                    <th class="columnText columnStatus{if $sortField == 'status'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='MyClaimList'}pageNo={@$pageNo}&sortField=status&sortOrder={if $sortField == 'status' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}cash.claim.user.status{/lang}</a></th>
                    <th class="columnText columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='MyClaimList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}cash.claim.user.time{/lang}</a></th>
                    <th class="columnText columnAmount{if $sortField == 'amount'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='MyClaimList'}pageNo={@$pageNo}&sortField=amount&sortOrder={if $sortField == 'amount' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}cash.claim.user.amount{/lang}</a></th>
                    <th class="columnText columnCurrency{if $sortField == 'currency'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='MyClaimList'}pageNo={@$pageNo}&sortField=currency&sortOrder={if $sortField == 'currency' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}cash.claim.user.currency{/lang}</a></th>
                    <th class="columnText columnSubject{if $sortField == 'origSubject'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='MyClaimList'}pageNo={@$pageNo}&sortField=origSubject&sortOrder={if $sortField == 'origSubject' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}cash.claim.user.subject{/lang}</a></th>

                    {event name='columnHeads'}
                </tr>
            </thead>

            <tbody>
                {foreach from=$objects item=claim}
                    <tr class="jsClaimRow">
                        <td class="columnIcon">
                            {if $claim->status == 1}
                                <span class="icon icon24 fa-money jsPayButton jsTooltip pointer" title="{lang}cash.claim.user.pay{/lang}" data-object-id="{@$claim->userClaimID}"></span>
                            {else}
                                <span class="icon icon24 fa-money disabled"></span>
                            {/if}

                            {if $claim->isTransfer}
                                <span class="icon icon24 fa-bell jsTooltip pointer" title="{lang}cash.claim.user.transfer{/lang}"></span>
                            {else}
                                <span class="icon icon24 fa-bell disabled"></span>
                            {/if}

                            {if $claim->status == 1 && $userBalance[$claim->currency]|isset && $userBalance[$claim->currency] > $claim->amount}
                                <span class="icon icon24 fa-exchange jsBalanceButton jsTooltip pointer" title="{lang}cash.claim.user.balance{/lang}" data-object-id="{@$claim->userClaimID}"></span>
                            {else}
                                <span class="icon icon24 fa-exchange disabled"></span>
                            {/if}
                            <a href="{link controller='Claim' application='cash' object=$claim->getClaim()}{/link}"><span class="icon icon24 fa-eye jsTooltip pointer" title="{lang}cash.claim.open{/lang}"></span></a>
                        </td>
                        <td class="columnID columnCashID">{$claim->userClaimID}</td>
                        {if $claim->status == 0}
                            <td class="columnText columnStatus"><span class="label badge grey">{lang}cash.claim.user.status.pending{/lang}</span></td>
                        {elseif $claim->status == 1}
                            <td class="columnText columnStatus"><span class="label badge red">{lang}cash.claim.user.status.open{/lang}</span></td>
                        {else}
                            <td class="columnText columnStatus"><span class="label badge green">{lang}cash.claim.user.status.paid{/lang}</span></td>
                        {/if}

                        <td class="columnText columnTime">{@$claim->time|time}</td>
                        <td class="columnDigits columnAmount">{$claim->amount|currency}</td>
                        <td class="columnText columnCurrency">{$claim->currency}</td>
                        <td class="columnText columnSubject">{if !$claim->subject|empty}<del>{$claim->origSubject}</del><br>{$claim->subject}{else}{$claim->origSubject}{/if}</td>

                        {event name='columns'}
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
{else}
    <p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

<footer class="contentFooter">
    {hascontent}
        <div class="paginationBottom">
            {content}{@$pagesLinks}{/content}
        </div>
    {/hascontent}

    {hascontent}
        <nav class="contentFooterNavigation">
            <ul>
                {content}

                    {event name='contentFooterNavigation'}
                {/content}
            </ul>
        </nav>
    {/hascontent}
</footer>

<script data-relocate="true">
    WCF.Language.addObject({
        'cash.claim.user.pay.title':        '{jslang}cash.claim.user.pay.title{/jslang}',
        'cash.claim.user.balance.confirm':    '{jslang}cash.claim.user.balance.confirm{/jslang}'
    });

    require(['UZ/Cash/ClaimPay'], function (UZCashClaimPay) {
        UZCashClaimPay.init();
    });

    require(['UZ/Cash/UserClaimBalance'], function (UZCashUserClaimBalance) {
        UZCashUserClaimBalance.init();
    });
</script>

{include file='footer'}
