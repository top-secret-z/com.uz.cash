{capture assign='pageTitle'}{lang}cash.posting.postings{/lang}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='contentTitle'}{lang}cash.posting.postings{/lang} <span class="badge">{#$items}</span> {if !$balance|empty}<span class="badge">{$balance}</span>{/if}{/capture}

{capture assign='contentHeaderNavigation'}
	{if $__wcf->session->getPermission('user.cash.canManage')}
		<li><a href="{link application='cash' controller='PostingAdd'}{/link}" class="button buttonPrimary"><span class="icon icon16 fa-plus"></span> <span>{lang}cash.posting.add{/lang}</span></a></li>
	{/if}
{/capture}

{assign var='linkParameters' value=''}
{if $categoryID}{capture append=linkParameters}&categoryID={@$categoryID}{/capture}{/if}
{if $currency}{capture append=linkParameters}&currency={@$currency|rawurlencode}{/capture}{/if}
{if $subject}{capture append=linkParameters}&subject={@$subject|rawurlencode}{/capture}{/if}
{if $type}{capture append=linkParameters}&type={@$type|rawurlencode}{/capture}{/if}
{if $startDate}{capture append=linkParameters}&startDate={@$startDate|rawurlencode}{/capture}{/if}
{if $endDate}{capture append=linkParameters}&endDate={@$endDate|rawurlencode}{/capture}{/if}

{capture assign='contentInteractionPagination'}
	{pages print=true assign=pagesLinks application="cash" controller="PostingList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
{/capture}

{capture assign='contentInteractionDropdownItems'}
	{if $__wcf->session->getPermission('user.cash.canManage')}
		<li><a rel="alternate" href="{link controller='PostingExport' application='cash' categoryID=$categoryID currency=$currency subject=$subject type=$type startDate=$startDate endDate=$endDate}{/link}">{lang}cash.posting.export{/lang}</a></li>
	{/if}
{/capture}

{include file='header'}

<form method="post" action="{link controller='PostingList' application='cash'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>
		
		<div class="row rowColGap formGrid">
			{if $availableCategories|count > 1}
				<dl class="col-xs-12 col-md-4">
					<dt></dt>
					<dd>
						<select name="categoryID" id="categoryID">
							<option value="">{lang}cash.posting.filter.categoryID{/lang}</option>
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
							<option value="">{lang}cash.posting.filter.currency{/lang}</option>
							{htmlOptions options=$availableCurrencies selected=$currency}
						</select>
					</dd>
				</dl>
			{/if}
			
			{if $availableTypes|count > 1}
				<dl class="col-xs-12 col-md-4">
					<dt></dt>
					<dd>
						<select name="type" id="type">
							<option value="">{lang}cash.posting.filter.type{/lang}</option>
							{htmlOptions options=$availableTypes selected=$type}
						</select>
					</dd>
				</dl>
			{/if}
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="subject" name="subject" value="{$subject}" placeholder="{lang}cash.posting.add.subject{/lang}" class="long">
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="date" id="startDate" name="startDate" value="{$startDate}" data-placeholder="{lang}wcf.date.period.start{/lang}">
				</dd>
			</dl>
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="date" id="endDate" name="endDate" value="{$endDate}" data-placeholder="{lang}wcf.date.period.end{/lang}">
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
		<table class="table jsObjectActionContainer" data-object-action-class-name="cash\data\cash\posting\CashPostingAction">
			<thead>
				<tr>
					<th class="columnID columnPostingID{if $sortField == 'postingID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link application='cash' controller='PostingList'}pageNo={@$pageNo}&sortField=postingID&sortOrder={if $sortField == 'postingID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='PostingList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.posting.add.time{/lang}</a></th>
					<th class="columnText columnType{if $sortField == 'type'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='PostingList'}pageNo={@$pageNo}&sortField=type&sortOrder={if $sortField == 'type' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.posting.add.type{/lang}</a></th>
					<th class="columnText columnCategory{if $sortField == 'categoryID'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='PostingList'}pageNo={@$pageNo}&sortField=categoryID&sortOrder={if $sortField == 'categoryID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.posting.add.categoryID{/lang}</a></th>
					<th class="columnText columnAmount{if $sortField == 'amount'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='PostingList'}pageNo={@$pageNo}&sortField=amount&sortOrder={if $sortField == 'amount' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.posting.add.amount{/lang}</a></th>
					<th class="columnText columnCurrency{if $sortField == 'currency'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='PostingList'}pageNo={@$pageNo}&sortField=currency&sortOrder={if $sortField == 'currency' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.posting.add.currency{/lang}</a></th>
					<th class="columnText columnSubject{if $sortField == 'subject'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='PostingList'}pageNo={@$pageNo}&sortField=subject&sortOrder={if $sortField == 'subject' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.posting.add.subject{/lang}</a></th>
				</tr>
			</thead>
			
			<tbody class="jsReloadPageWhenEmpty">
				{foreach from=$objects item=posting}
					<tr class="jsPostingRow jsObjectActionObject" data-object-id="{@$posting->getObjectID()}">
						<td class="columnIcon">
							<a href="{link controller='PostingEdit' application='cash' object=$posting}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							{objectAction action="delete" objectTitle=$posting->getTitle()}
							<a href="{link controller='Posting' application='cash' object=$posting}{/link}"><span class="icon icon16 fa-eye jsTooltip pointer" title="{lang}cash.posting.open{/lang}"></span></a>
						</td>
						<td class="columnID columnPostingID">{$posting->postingID}</td>
						<td class="columnText columnTime">{@$posting->time|time}</td>
						{if $posting->type == 'expense'}
							<td class="columnText columnType"><span class="label badge red">{lang}cash.posting.add.type.{$posting->type}{/lang}</span></td>
						{else}
							<td class="columnText columnType"><span class="label badge green">{lang}cash.posting.add.type.{$posting->type}{/lang}</span></td>
						{/if}
						<td class="columnText columnCategory">{lang}{$availableCategories[$posting->categoryID]}{/lang}</td>
						<td class="columnText columnAmount">{$posting->amount|currency}</td>
						<td class="columnText columnCurrency">{$posting->currency}</td>
						<td class="columnText columnSubject">{$posting->subject}</td>
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
					<li><a href="{link application='cash' controller='PostingAdd'}{/link}" class="button buttonPrimary"><span class="icon icon16 fa-plus"></span> <span>{lang}cash.posting.add{/lang}</span></a></li>
					
					{event name='contentFooterNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

{include file='footer'}
