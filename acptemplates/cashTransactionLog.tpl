{capture assign='pageTitle'}{lang}cash.acp.transaction.log{/lang}: {@$log->logID}{/capture}
{include file='header'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}cash.acp.transaction.log{/lang}: {@$log->logID}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='CashTransactionLogList' application='cash'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}cash.acp.menu.link.cash.transaction.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<section class="section">
	<h2 class="sectionTitle">{lang}cash.acp.transaction.log{/lang}: {@$log->logID}</h2>
	
	<dl>
		<dt>{lang}cash.acp.transaction.log.message{/lang}</dt>
		<dd>{$log->logMessage}</dd>
		
		{if $log->userID}
			<dt>{lang}wcf.user.username{/lang}</dt>
			<dd><a href="{link controller='UserEdit' id=$log->userID}{/link}" title="{lang}wcf.acp.user.edit{/lang}">{$log->getUser()->username}</a></dd>
		{/if}
		
		{if $log->userClaimID}
			<dt>{lang}cash.acp.transaction.log.claim{/lang}</dt>
			<dd>{$log->getUserClaimSubject()}</dd>
		{/if}
		
		<dt>{lang}cash.acp.transaction.log.paymentMethod{/lang}</dt>
		<dd>{lang}wcf.payment.{@$log->getPaymentMethodName()}{/lang}</dd>
		
		<dt>{lang}cash.acp.transaction.log.transactionID{/lang}</dt>
		<dd>{$log->transactionID}</dd>
		
		<dt>{lang}cash.acp.transaction.log.time{/lang}</dt>
		<dd>{@$log->logTime|time}</dd>
	</dl>
</section>

<section class="section">
	<h2 class="sectionTitle">{lang}cash.acp.transaction.log.transactionDetails{/lang}</h2>

	<dl>
		{foreach from=$log->getTransactionDetails() key=key item=value}
			<dt>{$key}</dt>
			<dd>{$value}</dd>
		{/foreach}
	</dl>
</section>

{event name='sections'}

<footer class="contentFooter">
	<nav class="contentFooterNavigation">
		<ul>
			<li><a href="{link controller='CashTransactionLogList' application='cash'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}cash.acp.menu.link.cash.transaction.list{/lang}</span></a></li>
			
			{event name='contentFooterNavigation'}
		</ul>
	</nav>
</footer>

{include file='footer'}
