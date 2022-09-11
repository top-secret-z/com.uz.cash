{capture assign='pageTitle'}{lang}cash.cash.overview{/lang}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='contentTitle'}{lang}cash.cash.overview{/lang} <span class="badge">{#$items}</span>{/capture}

{capture assign='sidebarRight'}
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
	
	{event name='boxes'}
{/capture}

{assign var='linkParameters' value=''}
{if $comment}{capture append=linkParameters}&comment={@$comment|rawurlencode}{/capture}{/if}
{if $currency}{capture append=linkParameters}&currency={@$currency|rawurlencode}{/capture}{/if}
{if $type}{capture append=linkParameters}&type={@$type|rawurlencode}{/capture}{/if}
{if $username}{capture append=linkParameters}&username={@$username|rawurlencode}{/capture}{/if}
{if $startDate}{capture append=linkParameters}&startDate={@$startDate|rawurlencode}{/capture}{/if}
{if $endDate}{capture append=linkParameters}&endDate={@$endDate|rawurlencode}{/capture}{/if}

{capture assign='contentInteractionPagination'}
	{pages print=true assign=pagesLinks controller="Overview" application="cash" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
{/capture}

{capture assign='contentInteractionDropdownItems'}
	{if $__wcf->session->getPermission('user.cash.canManage')}
		<li><a rel="alternate" href="{link application='cash' controller='CashExport'}{/link}">{lang}cash.cash.export{/lang}</a></li>
	{/if}
{/capture}

{include file='header'}

{if $__wcf->getSession()->getPermission('user.cash.canManage')}
	<form method="post" action="{link application='cash' controller='Overview'}{/link}">
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>
			
			<div class="row rowColGap formGrid">
				{if $availableTypes|count > 1}
					<dl class="col-xs-12 col-md-4">
						<dt></dt>
						<dd>
							<select name="type" id="type">
								<option value="">{lang}cash.cash.filter.type{/lang}</option>
								{htmlOptions options=$availableTypes selected=$type}
							</select>
						</dd>
					</dl>
				{/if}
				
				<dl class="col-xs-12 col-md-4">
					<dt></dt>
					<dd>
						<input type="text" id="username" name="username" value="{$username}" placeholder="{lang}cash.cash.filter.username{/lang}" class="long">
					</dd>
				</dl>
				
				{if $availableCurrencies|count > 1}
					<dl class="col-xs-12 col-md-4">
						<dt></dt>
						<dd>
							<select name="currency" id="currency">
								<option value="">{lang}cash.cash.filter.currency{/lang}</option>
								{htmlOptions options=$availableCurrencies selected=$currency}
							</select>
						</dd>
					</dl>
				{/if}
				
				<dl class="col-xs-12 col-md-4">
					<dt></dt>
					<dd>
						<input type="text" id="comment" name="comment" value="{$comment}" placeholder="{lang}cash.cash.filter.comment{/lang}" class="long">
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
{/if}

{if $objects|count}
	{assign var=canSee value=$__wcf->getSession()->getPermission('user.cash.canManage')}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnCashID{if $sortField == 'cashID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link application='cash' controller='Overview'}pageNo={@$pageNo}&sortField=cashID&sortOrder={if $sortField == 'cashID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='Overview'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.cash.time{/lang}</a></th>
					<th class="columnText columnType{if $sortField == 'type'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='Overview'}pageNo={@$pageNo}&sortField=type&sortOrder={if $sortField == 'type' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.cash.type{/lang}</a></th>
					<th class="columnText columnUsername{if $sortField == 'username'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='Overview'}pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.cash.username{/lang}</a></th>
					<th class="columnText columnAmount{if $sortField == 'amount'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='Overview'}pageNo={@$pageNo}&sortField=amount&sortOrder={if $sortField == 'amount' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.cash.amount{/lang}</a></th>
					<th class="columnText columnCurrency{if $sortField == 'currency'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='Overview'}pageNo={@$pageNo}&sortField=currency&sortOrder={if $sortField == 'currency' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.cash.currency{/lang}</a></th>
					<th class="columnText columnComment{if $sortField == 'comment'} active {@$sortOrder}{/if}"><a href="{link application='cash' controller='Overview'}pageNo={@$pageNo}&sortField=comment&sortOrder={if $sortField == 'comment' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}cash.cash.comment{/lang}</a></th>
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=cash}
					<tr class="jsCashRow">
						<td class="columnIcon">
							{if $cash->type == 'posting' && $cash->typeID}
								<a href="{link controller='Posting' application='cash' id=$cash->typeID}{/link}"><span class="icon icon16 fa-eye jsTooltip pointer" title="{lang}cash.posting.open{/lang}"></span></a>
							{elseif $cash->type|strpos:claim === 0 && ($canSee || $__wcf->user->userID == $cash->userID) && $cash->typeID}
								<a href="{link controller='Claim' application='cash' typeID=$cash->typeID}{/link}"><span class="icon icon16 fa-eye jsTooltip pointer" title="{lang}cash.claim.open{/lang}"></span></a>
							{elseif $cash->type|strpos:credit === 0 && ($canSee || $__wcf->user->userID == $cash->userID) && $cash->typeID}
								<a href="{link controller='Credit' application='cash' typeID=$cash->typeID}{/link}"><span class="icon icon16 fa-eye jsTooltip pointer" title="{lang}cash.credit.open{/lang}"></span></a>
							{else}
								<span class="icon icon16 fa-eye disabled"></span>
							{/if}
						</td>
						<td class="columnID columnCashID">{$cash->cashID}</td>
						<td class="columnText columnTime">{@$cash->time|time}</td>
						<td class="columnText columnType">{lang}cash.cash.type.{$cash->type}{/lang}</td>
						{if $canSee || $__wcf->user->userID == $cash->userID || $cash->type == 'posting'}
							<td class="columnText columnUsername">{$cash->username}</td>
						{else}
							<td class="columnText columnUsername">{lang}cash.cash.hidden{/lang}</td>
						{/if}
						<td class="columnDigits columnAmount">{$cash->amount|currency}</td>
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
