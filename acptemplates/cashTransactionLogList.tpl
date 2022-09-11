{include file='header' pageTitle='cash.acp.transaction.log.list'}

<script data-relocate="true">
	$(function() {
		new WCF.Search.User('#username');
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}cash.acp.transaction.log.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

<form method="post" action="{link controller='CashTransactionLogList' application='cash'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>
		
		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="transactionID" name="transactionID" value="{$transactionID}" placeholder="{lang}cash.acp.transaction.log.transactionID{/lang}" class="long">
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="username" name="username" value="{$username}" placeholder="{lang}wcf.user.username{/lang}" class="long">
				</dd>
			</dl>
			
			{event name='filterFields'}
		</div>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			{csrfToken}
		</div>
	</section>
</form>

{hascontent}
	<div class="paginationTop">
		{content}
			{assign var='linkParameters' value=''}
			{if $transactionID}{capture append=linkParameters}&transactionID={@$transactionID|rawurlencode}{/capture}{/if}
			{if $username}{capture append=linkParameters}&username={@$username|rawurlencode}{/capture}{/if}
			{if $userClaimID}{capture append=linkParameters}&userClaimID={@$userClaimID}{/capture}{/if}
			
			{pages print=true assign=pagesLinks controller='CashTransactionLogList' application='cash' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnLogID{if $sortField == 'logID'} active {@$sortOrder}{/if}"><a href="{link controller='CashTransactionLogList' application='cash'}pageNo={@$pageNo}&sortField=logID&sortOrder={if $sortField == 'logID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnLogMessage{if $sortField == 'logMessage'} active {@$sortOrder}{/if}"><a href="{link controller='CashTransactionLogList' application='cash'}pageNo={@$pageNo}&sortField=logMessage&sortOrder={if $sortField == 'logMessage' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.acp.transaction.log.message{/lang}</a></th>
					<th class="columnText columnUsername{if $sortField == 'userID'} active {@$sortOrder}{/if}"><a href="{link controller='CashTransactionLogList' application='cash'}pageNo={@$pageNo}&sortField=userID&sortOrder={if $sortField == 'userID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.user.username{/lang}</a></th>
					<th class="columnText columnSubject{if $sortField == 'userClaimID'} active {@$sortOrder}{/if}"><a href="{link controller='CashTransactionLogList' application='cash'}pageNo={@$pageNo}&sortField=userClaimID&sortOrder={if $sortField == 'userClaimID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.acp.transaction.log.claim{/lang}</a></th>
					<th class="columnText columnPaymentMethod{if $sortField == 'paymentMethodObjectTypeID'} active {@$sortOrder}{/if}"><a href="{link controller='CashTransactionLogList' application='cash'}pageNo={@$pageNo}&sortField=paymentMethodObjectTypeID&sortOrder={if $sortField == 'paymentMethodObjectTypeID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.acp.transaction.log.paymentMethod{/lang}</a></th>
					<th class="columnText columnTransactionID{if $sortField == 'transactionID'} active {@$sortOrder}{/if}"><a href="{link controller='CashTransactionLogList' application='cash'}pageNo={@$pageNo}&sortField=transactionID&sortOrder={if $sortField == 'transactionID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.acp.transaction.log.transactionID{/lang}</a></th>
					<th class="columnDate columnLogTime{if $sortField == 'logTime'} active {@$sortOrder}{/if}"><a href="{link controller='CashTransactionLogList' application='cash'}pageNo={@$pageNo}&sortField=logTime&sortOrder={if $sortField == 'logTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.acp.transaction.log.time{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=log}
					<tr>
						<td class="columnID columnLogID">{@$log->logID}</td>
						<td class="columnTitle columnLogMessage"><a href="{link controller='CashTransactionLog'  application='cash' id=$log->logID}{/link}">{$log->logMessage}</a></td>
						<td class="columnText columnUsername"><a href="{link controller='UserEdit' id=$log->userID}{/link}" title="{lang}wcf.acp.user.edit{/lang}">{$log->username}</a></td>
						<td class="columnText columnSubject">{if $log->subject|empty}{$log->origSubject}{else}{$log->subject}{/if}</td>
						<td class="columnText columnPaymentMethod">{lang}wcf.payment.{@$log->getPaymentMethodName()}{/lang}</td>
						<td class="columnText columnTransactionID">{$log->transactionID}</td>
						<td class="columnDate columnLogTime">{@$log->logTime|time}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
		
	</div>
	
	<footer class="contentFooter">
		{hascontent}
			<div class="paginationBottom">
				{content}{@$pagesLinks}{/content}
			</div>
		{/hascontent}
		
		{hascontent}
			<nav class="contentFooterNavigation">
				<ul>
					{content}{event name='contentFooterNavigation'}{/content}
				</ul>
			</nav>
		{/hascontent}
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
