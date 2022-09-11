{capture assign='pageTitle'}{lang}cash.claim.{@$action}{/lang}{/capture}

{capture assign='contentTitle'}{lang}cash.claim.{@$action}{/lang}{/capture}

{capture assign='contentHeaderNavigation'}
	<li><a href="{link application='cash' controller='ClaimList'}{/link}" class="button buttonPrimary"><span class="icon icon16 fa-files-o"></span> <span>{lang}cash.claim.list{/lang}</span></a></li>
{/capture}

{include file='header'}

{include file='formError'}

{if $categoryWarning}
	<p class="error">{lang}cash.claim.add.category.warning{/lang}</p>
{elseif $currencyWarning}
	<p class="error">{lang}cash.claim.add.currency.warning{/lang}</p>
{else}
	{if $action == 'edit'}
		<p class="warning">{lang}cash.claim.edit.warning{/lang}</p>
	{/if}
	
	<form id="messageContainer" class="jsFormGuard" method="post" action="{if $action == 'add'}{link application='cash' controller='ClaimAdd'}{/link}{else}{link application='cash' controller='ClaimEdit' id=$claimID}{/link}{/if}">
		<section class="section">
			<h2 class="sectionTitle">{lang}cash.claim.add.general{/lang}</h2>
			
			<dl{if $errorField == 'subject'} class="formError"{/if}>
				<dt><label for="subject">{lang}cash.claim.add.subject{/lang}</label></dt>
				<dd>
					<input type="text" id="subject" name="subject" value="{$subject}" maxlength="255" class="long">
					{if $errorField == 'subject'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}cash.claim.add.subject.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl{if $errorField == 'categoryID'} class="formError"{/if}>
				<dt><label for="categoryID">{lang}cash.claim.add.categoryID{/lang}</label></dt>
				<dd>
					<select id="categoryID" name="categoryID">
						{include file='categoryOptionList'}
					</select>
					
					{if $errorField == 'categoryID'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}cash.claim.add.categoryID.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
		</section>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}cash.claim.add.amount{/lang}</h2>
			
			<dl {if $errorField == 'amount'} class="formError"{/if}>
				<dt><label for="amount">{lang}cash.claim.add.amount{/lang}</label></dt>
				<dd>
					<input type="text" id="amount" name="amount" value="{@$amount|currency}" class="tiny">
					{if $errorField == 'amount'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}cash.claim.add.amount.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl {if $errorField == 'currency'} class="formError"{/if}>
				<dt><label for="currency">{lang}cash.claim.add.currency{/lang}</label></dt>
				<dd>
					<select name="currency" id="currency">
						{htmlOptions output=$availableCurrencies values=$availableCurrencies selected=$currency}
					</select>
					
					{if $errorField == 'currency'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}cash.claim.add.currency.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl>
				<dt><label for="excludedPaymentMethods">{lang}cash.claim.add.excludedPaymentMethods{/lang}</label></dt>
				<dd>
					{foreach from=$availablePaymentMethods key=$objectType item=$paymentMethod}
						<div>
							<label>
								<label><input type="checkbox" name="excludedPaymentMethods[{$objectType}]" value="1"{if $objectType|in_array:$excludedPaymentMethods} checked="checked"{/if}> {@$paymentMethod}</label>
							</label>
						</div>
					{/foreach}
					<small>{lang}cash.claim.add.excludedPaymentMethods.description{/lang}</small>
				</dd>
			</dl>
		</section>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}cash.claim.add.execution{/lang}</h2>
				<dl{if $errorField == 'executionTime'} class="formError"{/if}>
				<dt><label for="executionTime">{lang}cash.claim.add.execution.time{/lang}</label></dt>
				<dd>
					<input type="datetime" id="executionTime" name="executionTime" value="{$executionTime}" class="medium" data-ignore-timezone="true" data-disable-clear="true">
					{if $errorField == 'executionTime'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}cash.claim.add.execution.time.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl{if $errorField == 'timezone'} class="formError"{/if}>
				<dt><label for="timezone">{lang}cash.claim.add.execution.timezone{/lang}</label></dt>
				<dd>
					<select name="timezone" id="timezone">
						{htmlOptions options=$availableTimezones selected=$timezone}
					</select>
					{if $errorField == 'timezone'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}cash.claim.add.execution.timezone.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl>
				<dt><label for="frequency">{lang}cash.claim.add.frequency{/lang}</label></dt>
				<dd>
					<select name="frequency" id="frequency">
						<option value="once"{if $frequency == 'once'} selected="selected"{/if}>{lang}cash.claim.add.frequency.once{/lang}</option>
						<option value="week"{if $frequency == 'week'} selected="selected"{/if}>{lang}cash.claim.add.frequency.week{/lang}</option>
						<option value="twoweek"{if $frequency == 'twoweek'} selected="selected"{/if}>{lang}cash.claim.add.frequency.twoweek{/lang}</option>
						<option value="month"{if $frequency == 'month'} selected="selected"{/if}>{lang}cash.claim.add.frequency.month{/lang}</option>
						<option value="twomonth"{if $frequency == 'twomonth'} selected="selected"{/if}>{lang}cash.claim.add.frequency.twomonth{/lang}</option>
						<option value="threemonth"{if $frequency == 'quarter'} selected="selected"{/if}>{lang}cash.claim.add.frequency.threemonth{/lang}</option>
						<option value="sixmonth"{if $frequency == 'halfyear'} selected="selected"{/if}>{lang}cash.claim.add.frequency.sixmonth{/lang}</option>
						<option value="year"{if $frequency == 'year'} selected="selected"{/if}>{lang}cash.claim.add.frequency.year{/lang}</option>
					</select>
				</dd>
			</dl>
			
			<dl id="executionsDL">
				<dt><label for="executions">{lang}cash.claim.add.executions{/lang}</label></dt>
				<dd>
					<input type="number" id="executions" name="executions" value="{$executions}" class="tiny" minvalue="1">
				</dd>
			</dl>
		</section>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}cash.claim.add.users{/lang}</h2>
			
			<dl{if $errorField == 'users'} class="formError"{/if}>
				<dt><label for="users">{lang}cash.claim.add.usernames{/lang}</label></dt>
				<dd>
					<textarea id="users" name="users" class="long" cols="40" rows="2">{$users}</textarea>
					{if $errorField == 'users'}
						<small class="innerError">
							{if $errorType|is_array}
								{foreach from=$errorType item='errorData'}
									{lang}cash.claim.add.usernames.error.{@$errorData.type}{/lang}
								{/foreach}
							{else}
								{lang}cash.claim.add.usernames.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}cash.claim.add.usernames.description{/lang}</small>
				</dd>
			</dl>
			
			<div class="section tabMenuContainer">
				<nav class="tabMenu">
					<ul>
						{foreach from=$userConditions key='conditionGroup' item='conditionObjectTypes'}
							<li><a href="#user_{$conditionGroup|rawurlencode}">{lang}wcf.user.condition.conditionGroup.{$conditionGroup}{/lang}</a></li>
						{/foreach}
					</ul>
				</nav>
				
				{foreach from=$userConditions key='conditionGroup' item='conditionObjectTypes'}
					<div id="user_{$conditionGroup}" class="tabMenuContent">
						<section class="section">
							<h2 class="sectionTitle">{lang}wcf.user.condition.conditionGroup.{$conditionGroup}{/lang}</h2>
							
							{foreach from=$conditionObjectTypes item='condition'}
								{@$condition->getProcessor()->getHtml()}
							{/foreach}
						</section>
					</div>
				{/foreach}
			</div>
		</section>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}cash.claim.add.message{/lang}</h2>
			
			<dl class="wide{if $errorField == 'text'} formError{/if}">
				<dt><label for="text">{lang}cash.claim.add.message{/lang}</label></dt>
				<dd>
					<textarea id="text" name="text" rows="20" cols="40">{$text}</textarea>
					{if $errorField == 'text'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{elseif $errorType == 'tooLong'}
								{lang}wcf.message.error.tooLong{/lang}
							{elseif $errorType == 'censoredWordsFound'}
								{lang}wcf.message.error.censoredWordsFound{/lang}
							{elseif $errorType == 'disallowedBBCodes'}
								{lang}wcf.message.error.disallowedBBCodes{/lang}
							{else}
								{lang}cash.claim.add.message.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			{event name='messageFields'}
		</section>
			
		{include file='messageFormTabs' wysiwygContainerID='text'}
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			
			{if $action == 'edit'}
				{include file='messageFormPreviewButton' previewMessageObjectType='com.uz.cash.claim' previewMessageObjectID=$claim->claimID}
			{else}
				{include file='messageFormPreviewButton' previewMessageObjectType='com.uz.cash.claim' previewMessageObjectID=0}
			{/if}
			{csrfToken}
		</div>
	</form>
{/if}

<script data-relocate="true">
	$(function() {
		var $frequeny = $('#frequency').change(function(event) {
			var $value = $(event.currentTarget).val();
			$('#executionsDL').hide();
			
			if ($value != 'once') {
				$('#executionsDL').show();
			}
		});
		
		$frequeny.trigger('change');
	});
</script>

<script data-relocate="true">
	$(function() {
		new WCF.Message.FormGuard();
		
		new WCF.Search.User('#users', null, false, [ ], true);
	});
</script>

{include file='wysiwyg'}
{include file='footer'}
