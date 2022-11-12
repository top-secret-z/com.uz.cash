{capture assign='pageTitle'}{lang}cash.claim.user{/lang}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='contentTitle'}{lang}cash.claim.user{/lang} <span class="badge">{#$items}</span>{/capture}

{assign var='linkParameters' value=''}
{if $username}{capture append=linkParameters}&username={@$username|rawurlencode}{/capture}{/if}
{if $status > -1}{capture append=linkParameters}&status={@$status}{/capture}{/if}
{if $currency}{capture append=linkParameters}&currency={@$currency|rawurlencode}{/capture}{/if}
{if $subject}{capture append=linkParameters}&subject={@$subject|rawurlencode}{/capture}{/if}
{if $isTransfer > -1}{capture append=linkParameters}&isTransfer={@$isTransfer}{/capture}{/if}

{capture assign='contentInteractionPagination'}
    {pages print=true assign=pagesLinks controller="UserClaimList" application="cash" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
{/capture}

{include file='header'}

<form method="post" action="{link controller='UserClaimList' application='cash'}{/link}">
    <section class="section">
        <h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>

        <div class="row rowColGap formGrid">
            {if $availableStati|count > 1}
                <dl class="col-xs-12 col-md-4">
                    <dt></dt>
                    <dd>
                        <select name="status" id="status">
                            <option value="-1">{lang}cash.claim.user.filter.status{/lang}</option>
                            {htmlOptions options=$availableStati selected=$status}
                        </select>
                    </dd>
                </dl>
            {/if}

            <dl class="col-xs-12 col-md-4">
                    <dt></dt>
                    <dd>
                        <select name="isTransfer" id="isTransfer">
                            <option value="-1">{lang}cash.claim.user.filter.transfer{/lang}</option>
                            {htmlOptions options=$availableTransfers selected=$isTransfer}
                        </select>
                    </dd>
                </dl>

            <dl class="col-xs-12 col-md-4">
                <dt></dt>
                <dd>
                    <input type="text" id="username" name="username" value="{$username}" placeholder="{lang}cash.claim.user.username{/lang}" class="long">
                </dd>
            </dl>

            {if $availableCurrencies|count > 1}
                <dl class="col-xs-12 col-md-4">
                    <dt></dt>
                    <dd>
                        <select name="currency" id="currency">
                            <option value="">{lang}cash.claim.user.filter.currency{/lang}</option>
                            {htmlOptions options=$availableCurrencies selected=$currency}
                        </select>
                    </dd>
                </dl>
            {/if}

            <dl class="col-xs-12 col-md-4">
                <dt></dt>
                <dd>
                    <input type="text" id="subject" name="subject" value="{$subject}" placeholder="{lang}cash.claim.add.subject{/lang}" class="long">
                </dd>
            </dl>
        </div>

        <div class="formSubmit">
            <input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
            {csrfToken}
        </div>
    </section>
</form>

{if $objects|count}
    <div class="section tabularBox">
        <table class="table jsObjectActionContainer" data-object-action-class-name="cash\data\cash\claim\user\UserCashClaimAction">
            <thead>
                <tr>
                    <th class="columnID columnClaimID{if $sortField == 'userClaimID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link application='cash' controller='UserClaimList'}pageNo={@$pageNo}&sortField=userClaimID&sortOrder={if $sortField == 'userClaimID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
                    <th class="columnText columnStatus{if $sortField == 'status'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='UserClaimList'}pageNo={@$pageNo}&sortField=status&sortOrder={if $sortField == 'status' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.claim.user.status{/lang}</a></th>
                    <th class="columnText columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='UserClaimList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.claim.user.time{/lang}</a></th>
                    <th class="columnText columnUsername{if $sortField == 'username'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='UserClaimList'}pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.claim.user.username{/lang}</a></th>
                    <th class="columnText columnAmount{if $sortField == 'amount'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='UserClaimList'}pageNo={@$pageNo}&sortField=amount&sortOrder={if $sortField == 'amount' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.claim.user.amount{/lang}</a></th>
                    <th class="columnText columnCurrency{if $sortField == 'currency'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='UserClaimList'}pageNo={@$pageNo}&sortField=currency&sortOrder={if $sortField == 'currency' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.claim.user.currency{/lang}</a></th>
                    <th class="columnText columnSubject{if $sortField == 'origSubject'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='UserClaimList'}pageNo={@$pageNo}&sortField=origSubject&sortOrder={if $sortField == 'origSubject' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.claim.user.subject{/lang}</a></th>

                    {event name='columnHeads'}
                </tr>
            </thead>

            <tbody class="jsReloadPageWhenEmpty">
                {foreach from=$objects item=claim}
                    <tr class="jsClaimRow">
                    <tr class="jsClaimRow jsObjectActionObject" data-object-id="{@$claim->getObjectID()}">
                        <td class="columnIcon">
                            {if $claim->status == 0}
                                <span class="icon icon16 fa-square-o disabled"></span>
                                <span class="icon icon24 fa-exclamation disabled"></span>
                                <span class="icon icon16 fa-pencil disabled"></span>
                                {objectAction action="delete" objectTitle=$claim->getTitle()}
                            {elseif $claim->status == 1}
                                <span class="icon icon16 fa-square-o jsPayButton jsTooltip pointer" title="{lang}cash.claim.user.pay.mark{/lang}" data-object-id="{@$claim->userClaimID}"></span>

                                {if $claim->isTransfer}
                                    <span class="icon icon24 fa-exclamation jsTooltip pointer" title="{lang}cash.claim.user.transfer{/lang}"></span>
                                {else}
                                    <span class="icon icon24 fa-exclamation disabled"></span>
                                {/if}
                                <span class="icon icon16 fa-pencil jsClaimEditButton jsTooltip pointer" title="{lang}wcf.global.button.edit{/lang}" data-object-id="{@$claim->userClaimID}"></span>
                                {objectAction action="delete" objectTitle=$claim->getTitle()}
                            {elseif $claim->status == 2}
                                <span class="icon icon16 fa-check-square-o jsUnpayButton jsTooltip pointer" title="{lang}cash.claim.user.unpay.mark{/lang}" data-object-id="{@$claim->userClaimID}"></span>
                                <span class="icon icon24 fa-exclamation disabled"></span>
                                <span class="icon icon16 fa-pencil disabled"></span>
                                <span class="icon icon16 fa-remove disabled"></span>
                            {/if}
                            <a href="{link controller='Claim' application='cash' object=$claim->getClaim()}{/link}"><span class="icon icon16 fa-eye jsTooltip pointer" title="{lang}cash.claim.open{/lang}"></span></a>

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
                        <td class="columnText columnUsername">{$claim->username}</td>
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
        'cash.claim.user.edit':                '{jslang}cash.claim.user.edit{/jslang}',
        'cash.claim.user.data':                '{jslang}cash.claim.user.data{/jslang}',
        'cash.claim.user.amount':            '{jslang}cash.claim.user.amount{/jslang}',
        'cash.claim.user.amount.error':        '{jslang}cash.claim.user.amount.error{/jslang}',
        'cash.claim.user.currency':            '{jslang}cash.claim.user.currency{/jslang}',
        'cash.claim.user.subject':            '{jslang}cash.claim.user.subject{/jslang}',
        'cash.claim.user.subject.error':    '{jslang}cash.claim.user.subject.error{/jslang}',
        'cash.claim.user.success':            '{jslang}cash.claim.user.success{/jslang}',
        'wcf.global.button.cancel':            '{jslang}wcf.global.button.cancel{/jslang}',
        'cash.claim.user.pay.confirm':        '{jslang}cash.claim.user.pay.confirm{/jslang}',
        'cash.claim.user.unpay.confirm':    '{jslang}cash.claim.user.unpay.confirm{/jslang}'
    });

    require(['UZ/Cash/UserClaimEdit'], function (UZCashUserClaimEdit) {
        UZCashUserClaimEdit.init();
    });

    require(['UZ/Cash/UserClaimPay'], function (UZCashUserClaimPay) {
        UZCashUserClaimPay.init();
    });

    require(['UZ/Cash/UserClaimUnpay'], function (UZCashUserClaimUnpay) {
        UZCashUserClaimUnpay.init();
    });
</script>

{include file='footer'}
