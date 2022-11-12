{capture assign='pageTitle'}{lang}cash.credit.user{/lang}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='contentTitle'}{lang}cash.credit.user{/lang} <span class="badge">{#$items}</span>{/capture}

{assign var='linkParameters' value=''}
{if $username}{capture append=linkParameters}&username={@$username|rawurlencode}{/capture}{/if}
{if $status}{capture append=linkParameters}&status={@$status}{/capture}{/if}
{if $currency}{capture append=linkParameters}&currency={@$currency|rawurlencode}{/capture}{/if}
{if $subject}{capture append=linkParameters}&subject={@$subject|rawurlencode}{/capture}{/if}

{capture assign='contentInteractionPagination'}
            {pages print=true assign=pagesLinks controller="UserCreditList" application="cash" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
{/capture}

{include file='header'}

<form method="post" action="{link controller='UserCreditList' application='cash'}{/link}">
    <section class="section">
        <h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>

        <div class="row rowColGap formGrid">
            {if $availableStati|count > 1}
                <dl class="col-xs-12 col-md-4">
                    <dt></dt>
                    <dd>
                        <select name="status" id="status">
                            <option value="-1">{lang}cash.credit.user.filter.status{/lang}</option>
                            {htmlOptions options=$availableStati selected=$status}
                        </select>
                    </dd>
                </dl>
            {/if}

            <dl class="col-xs-12 col-md-4">
                <dt></dt>
                <dd>
                    <input type="text" id="username" name="username" value="{$username}" placeholder="{lang}cash.credit.user.username{/lang}" class="long">
                </dd>
            </dl>

            {if $availableCurrencies|count > 1}
                <dl class="col-xs-12 col-md-4">
                    <dt></dt>
                    <dd>
                        <select name="currency" id="currency">
                            <option value="">{lang}cash.credit.user.filter.currency{/lang}</option>
                            {htmlOptions options=$availableCurrencies selected=$currency}
                        </select>
                    </dd>
                </dl>
            {/if}

            <dl class="col-xs-12 col-md-4">
                <dt></dt>
                <dd>
                    <input type="text" id="subject" name="subject" value="{$subject}" placeholder="{lang}cash.credit.add.subject{/lang}" class="long">
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
        <table class="table">
            <thead>
                <tr>
                    <th class="columnID columnCreditID{if $sortField == 'userCreditID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link application='cash' controller='UserCreditList'}pageNo={@$pageNo}&sortField=userCreditID&sortOrder={if $sortField == 'userCreditID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
                    <th class="columnText columnStatus{if $sortField == 'status'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='UserCreditList'}pageNo={@$pageNo}&sortField=status&sortOrder={if $sortField == 'status' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.credit.user.status{/lang}</a></th>
                    <th class="columnText columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='UserCreditList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.credit.user.time{/lang}</a></th>
                    <th class="columnText columnUsername{if $sortField == 'username'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='UserCreditList'}pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.credit.user.username{/lang}</a></th>
                    <th class="columnText columnAmount{if $sortField == 'amount'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='UserCreditList'}pageNo={@$pageNo}&sortField=amount&sortOrder={if $sortField == 'amount' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.credit.user.amount{/lang}</a></th>
                    <th class="columnText columnCurrency{if $sortField == 'currency'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='UserCreditList'}pageNo={@$pageNo}&sortField=currency&sortOrder={if $sortField == 'currency' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.credit.user.currency{/lang}</a></th>
                    <th class="columnText columnSubject{if $sortField == 'origSubject'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='UserCreditList'}pageNo={@$pageNo}&sortField=origSubject&sortOrder={if $sortField == 'origSubject' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.credit.user.subject{/lang}</a></th>

                    {event name='columnHeads'}
                </tr>
            </thead>

            <tbody>
                {foreach from=$objects item=credit}
                    <tr class="jsCreditRow">
                        <td class="columnIcon">
                            {if $credit->status == 0}
                                <span class="icon icon16 fa-square-o disabled"></span>
                                <span class="icon icon16 fa-pencil disabled"></span>
                            {elseif $credit->status == 1}
                                <span class="icon icon16 fa-square-o jsCreditButton jsTooltip pointer" title="{lang}cash.credit.user.credit.mark{/lang}" data-object-id="{@$credit->userCreditID}"></span>
                                <span class="icon icon16 fa-pencil jsCreditEditButton jsTooltip pointer" title="{lang}wcf.global.button.edit{/lang}" data-object-id="{@$credit->userCreditID}"></span>
                            {elseif $credit->status == 2 || $credit->status == 3}
                                <span class="icon icon16 fa-check-square-o jsUncreditButton jsTooltip pointer" title="{lang}cash.credit.user.uncredit.mark{/lang}" data-object-id="{@$credit->userCreditID}"></span>
                                <span class="icon icon16 fa-pencil disabled"></span>
                            {/if}
                            <a href="{link controller='Credit' application='cash' object=$credit->getCredit()}{/link}"><span class="icon icon16 fa-eye jsTooltip pointer" title="{lang}cash.credit.open{/lang}"></span></a>

                        </td>
                        <td class="columnID columnCashID">{$credit->userCreditID}</td>
                        {if $credit->status == 0}
                            <td class="columnText columnStatus"><span class="label badge grey">{lang}cash.credit.user.status.pending{/lang}</span></td>
                        {elseif $credit->status == 1}
                            <td class="columnText columnStatus"><span class="label badge red">{lang}cash.credit.user.status.open{/lang}</span></td>
                        {elseif $credit->status == 2}
                            <td class="columnText columnStatus"><span class="label badge green">{lang}cash.credit.user.status.paid{/lang}</span></td>
                        {else}
                            <td class="columnText columnStatus"><span class="label badge green">{lang}cash.credit.user.status.changed{/lang}</span></td>
                        {/if}

                        <td class="columnText columnTime">{@$credit->time|time}</td>
                        <td class="columnText columnUsername">{$credit->username}</td>
                        <td class="columnDigits columnAmount">{$credit->amount|currency}</td>
                        <td class="columnText columnCurrency">{$credit->currency}</td>
                        <td class="columnText columnSubject">{if !$credit->subject|empty}<del>{$credit->origSubject}</del><br>{$credit->subject}{else}{$credit->origSubject}{/if}</td>

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
        'cash.credit.user.edit':                '{jslang}cash.credit.user.edit{/jslang}',
        'cash.credit.user.data':                '{jslang}cash.credit.user.data{/jslang}',
        'cash.credit.user.amount':                '{jslang}cash.credit.user.amount{/jslang}',
        'cash.credit.user.amount.error':        '{jslang}cash.credit.user.amount.error{/jslang}',
        'cash.credit.user.currency':            '{jslang}cash.credit.user.currency{/jslang}',
        'cash.credit.user.subject':                '{jslang}cash.credit.user.subject{/jslang}',
        'cash.credit.user.subject.error':        '{jslang}cash.credit.user.subject.error{/jslang}',
        'cash.credit.user.success':                '{jslang}cash.credit.user.success{/jslang}',
        'wcf.global.button.cancel':                '{jslang}wcf.global.button.cancel{/jslang}',
        'cash.credit.user.credit.confirm':        '{jslang}cash.credit.user.credit.confirm{/jslang}',
        'cash.credit.user.uncredit.confirm':    '{jslang}cash.credit.user.uncredit.confirm{/jslang}'
    });

    require(['UZ/Cash/UserCreditEdit'], function (UZCashUserCreditEdit) {
        UZCashUserCreditEdit.init();
    });

    require(['UZ/Cash/UserCreditCredit'], function (UZCashUserCreditCredit) {
        UZCashUserCreditCredit.init();
    });

    require(['UZ/Cash/UserCreditUncredit'], function (UZCashUserCreditUncredit) {
        UZCashUserCreditUncredit.init();
    });
</script>

{include file='footer'}
