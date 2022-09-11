{capture assign='pageTitle'}{lang}cash.credit.myCredits{/lang}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='contentTitle'}{lang}cash.credit.myCredits{/lang} <span class="badge">{#$items}</span>{/capture}

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
	{pages print=true assign=pagesLinks controller="MyCreditList" application="cash" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
{/capture}

{include file='header'}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnCreditID{if $sortField == 'userCreditID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link application='cash' controller='MyCreditList'}pageNo={@$pageNo}&sortField=userCreditID&sortOrder={if $sortField == 'userCreditID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText columnStatus{if $sortField == 'status'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='MyCreditList'}pageNo={@$pageNo}&sortField=status&sortOrder={if $sortField == 'status' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}cash.credit.user.status{/lang}</a></th>
					<th class="columnText columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='MyCreditList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}cash.credit.user.time{/lang}</a></th>
					<th class="columnText columnAmount{if $sortField == 'amount'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='MyCreditList'}pageNo={@$pageNo}&sortField=amount&sortOrder={if $sortField == 'amount' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}cash.credit.user.amount{/lang}</a></th>
					<th class="columnText columnCurrency{if $sortField == 'currency'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='MyCreditList'}pageNo={@$pageNo}&sortField=currency&sortOrder={if $sortField == 'currency' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}cash.credit.user.currency{/lang}</a></th>
					<th class="columnText columnSubject{if $sortField == 'origSubject'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='MyCreditList'}pageNo={@$pageNo}&sortField=origSubject&sortOrder={if $sortField == 'origSubject' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}cash.credit.user.subject{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=credit}
					<tr class="jsCreditRow">
						<td class="columnIcon">
							<a href="{link controller='Credit' application='cash' object=$credit->getCredit()}{/link}"><span class="icon icon24 fa-eye jsTooltip pointer" title="{lang}cash.credit.open{/lang}"></span></a>
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

{include file='footer'}
