{capture assign='pageTitle'}{lang}cash.cash.user.myAccount{/lang}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='contentTitle'}{lang}cash.cash.user.myAccount{/lang} <span class="badge">{#$items}</span>{/capture}

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
	
	{if $__wcf->getSession()->getPermission('user.cash.canSeeBalance')}
		<section class="box">
			<h2 class="boxTitle">{lang}cash.cash.box.cash{/lang}</h2>
			<p class="cashBoxSubHeadLine">{lang}cash.cash.box.cash.balance{/lang}</p>
			
			<div class="boxContent">
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
			</div>
			
			<p class="cashBoxSubHeadLine">{lang}cash.cash.box.cash.claims{/lang}</p>
			
			<div class="boxContent">
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
			</div>
		</section>
	{/if}
	
	{event name='boxes'}
{/capture}

{capture assign='contentInteractionPagination'}
	{pages print=true assign=pagesLinks controller="MyAccount" application="cash" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
{/capture}

{include file='header'}

{if $hasClaims}
	<p class="warning">{lang}cash.claim.hasOpen{/lang}</p>
{/if}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnCashID{if $sortField == 'cashID'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='MyAccount'}pageNo={@$pageNo}&sortField=cashID&sortOrder={if $sortField == 'cashID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='MyAccount'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}cash.cash.time{/lang}</a></th>
					<th class="columnText columnType{if $sortField == 'type'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='MyAccount'}pageNo={@$pageNo}&sortField=type&sortOrder={if $sortField == 'type' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}cash.cash.type{/lang}</a></th>
					<th class="columnText columnAmount{if $sortField == 'amount'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='MyAccount'}pageNo={@$pageNo}&sortField=amount&sortOrder={if $sortField == 'amount' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}cash.cash.amount{/lang}</a></th>
					<th class="columnText columnCurrency{if $sortField == 'currency'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='MyAccount'}pageNo={@$pageNo}&sortField=currency&sortOrder={if $sortField == 'currency' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}cash.cash.currency{/lang}</a></th>
					<th class="columnText columnComment{if $sortField == 'comment'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='MyAccount'}pageNo={@$pageNo}&sortField=comment&sortOrder={if $sortField == 'comment' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}cash.cash.comment{/lang}</a></th>
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=cash}
					{if $cash->type|substr:0:6 == 'credit'}
						{assign var='amount' value=-1*$cash->amount}
					{else}
						{assign var='amount' value=$cash->amount}
					{/if}
					<tr class="jsOptionRow">
						<td class="columnID columnCashID">{$cash->cashID}</td>
						<td class="columnText columnTime">{@$cash->time|time}</td>
						<td class="columnText columnType">{lang}cash.cash.type.{$cash->type}{/lang}</td>
						<td class="columnDigits columnAmount">{$amount|currency}</td>
						<td class="columnText columnCurrency">{$cash->currency}</td>
						<td class="columnText columnComment">{$cash->comment}</td>
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

{include file='footer'}
