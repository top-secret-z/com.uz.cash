{capture assign='pageTitle'}{lang}cash.claim.claims{/lang}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='contentTitle'}{lang}cash.claim.claims{/lang} <span class="badge">{#$items}</span>{/capture}

{capture assign='contentHeaderNavigation'}
    {if $__wcf->session->getPermission('user.cash.canManage')}
        <li><a href="{link application='cash' controller='ClaimAdd'}{/link}" class="button buttonPrimary"><span class="icon icon16 fa-plus"></span> <span>{lang}cash.claim.add{/lang}</span></a></li>
    {/if}
{/capture}

{assign var='linkParameters' value=''}
{if $categoryID}{capture append=linkParameters}&categoryID={@$categoryID}{/capture}{/if}
{if $currency}{capture append=linkParameters}&currency={@$currency|rawurlencode}{/capture}{/if}
{if $frequency}{capture append=linkParameters}&frequency={@$frequency|rawurlencode}{/capture}{/if}
{if $subject}{capture append=linkParameters}&subject={@$subject|rawurlencode}{/capture}{/if}

{capture assign='contentInteractionPagination'}
    {pages print=true assign=pagesLinks application="cash" controller="ClaimList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
{/capture}

{include file='header'}

<form method="post" action="{link controller='ClaimList' application='cash'}{/link}">
    <section class="section">
        <h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>

        <div class="row rowColGap formGrid">
            {if $availableCategories|count > 1}
                <dl class="col-xs-12 col-md-4">
                    <dt></dt>
                    <dd>
                        <select name="categoryID" id="categoryID">
                            <option value="">{lang}cash.claim.filter.categoryID{/lang}</option>
                            {htmlOptions options=$availableCategories selected=$categoryID}
                        </select>
                    </dd>
                </dl>
            {/if}

            {if $availableCurrencies|count > 1}
                <dl class="col-xs-12 col-md-4">
                    <dt></dt>
                    <dd>
                        <select name="currency" id="currency">
                            <option value="">{lang}cash.claim.filter.currency{/lang}</option>
                            {htmlOptions options=$availableCurrencies selected=$currency}
                        </select>
                    </dd>
                </dl>
            {/if}

            {if $availableFrequencies|count > 1}
                <dl class="col-xs-12 col-md-4">
                    <dt></dt>
                    <dd>
                        <select name="frequency" id="frequency">
                            <option value="">{lang}cash.claim.filter.frequency{/lang}</option>
                            {htmlOptions options=$availableFrequencies selected=$frequency}
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
        <table class="table jsObjectActionContainer" data-object-action-class-name="cash\data\cash\claim\CashClaimAction">
            <thead>
                <tr>
                    <th class="columnID columnClaimID{if $sortField == 'claimID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link application='cash' controller='ClaimList'}pageNo={@$pageNo}&sortField=claimID&sortOrder={if $sortField == 'claimID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
                    <th class="columnText columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='ClaimList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.claim.add.execution.time{/lang}</a></th>
                    <th class="columnText columnCategory{if $sortField == 'categoryID'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='PostingList'}pageNo={@$pageNo}&sortField=categoryID&sortOrder={if $sortField == 'categoryID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.claim.add.categoryID{/lang}</a></th>
                    <th class="columnText columnAmount{if $sortField == 'amount'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='ClaimList'}pageNo={@$pageNo}&sortField=amount&sortOrder={if $sortField == 'amount' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.claim.add.amount{/lang}</a></th>
                    <th class="columnText columnCurrency{if $sortField == 'currency'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='ClaimList'}pageNo={@$pageNo}&sortField=currency&sortOrder={if $sortField == 'currency' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.claim.add.currency{/lang}</a></th>
                    <th class="columnText columnFrequency{if $sortField == 'frequency'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='ClaimList'}pageNo={@$pageNo}&sortField=frequency&sortOrder={if $sortField == 'frequency' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.claim.add.frequency{/lang}</a></th>
                    <th class="columnText columnSubject{if $sortField == 'subject'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='ClaimList'}pageNo={@$pageNo}&sortField=subject&sortOrder={if $sortField == 'subject' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.claim.add.subject{/lang}</a></th>

                    {event name='columnHeads'}
                </tr>
            </thead>

            <tbody class="jsReloadPageWhenEmpty">
                {foreach from=$objects item=claim}
                    <tr class="jsClaimRow jsObjectActionObject" data-object-id="{@$claim->getObjectID()}">
                        <td class="columnIcon">
                            {objectAction action="toggle" isDisabled=$claim->isDisabled}
                            <a href="{link controller='ClaimEdit' application='cash' object=$claim}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
                            {if !$claim->executionCount}
                                {objectAction action="delete" objectTitle=$claim->getTitle()}
                            {else}
                                <span class="icon icon16 fa-remove disabled"></span>
                            {/if}
                            <a href="{link controller='Claim' application='cash' object=$claim}{/link}"><span class="icon icon16 fa-eye jsTooltip pointer" title="{lang}cash.claim.open{/lang}"></span></a>
                        </td>
                        <td class="columnID columnClaimID">{$claim->claimID}</td>
                        <td class="columnText columnTime">{@$claim->time|time}</td>
                        <td class="columnText columnCategory">{lang}{$availableCategories[$claim->categoryID]}{/lang}</td>
                        <td class="columnDigits columnAmount">{$claim->amount|currency}</td>
                        <td class="columnText columnCurrency">{$claim->currency}</td>
                        <td class="columnText columnFrequency">{lang}cash.claim.add.frequency.{$claim->frequency}{/lang}</td>
                        <td class="columnText columnSubject">{$claim->subject}</td>

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
                    <li><a href="{link application='cash' controller='ClaimAdd'}{/link}" class="button buttonPrimary"><span class="icon icon16 fa-plus"></span> <span>{lang}cash.claim.add{/lang}</span></a></li>

                    {event name='contentFooterNavigation'}
                {/content}
            </ul>
        </nav>
    {/hascontent}
</footer>

{include file='footer'}
