{if $allowTransfer}
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}cash.claim.user.pay.transfer{/lang}</h2>
		</header>
		
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" id="transferEnable"> {lang}cash.claim.user.pay.transfer.check{/lang}</label>
			</dd>
		</dl>
		
		<dl>
			<dt>{lang}cash.claim.user.pay.transfer.reference{/lang}</dt>
			<dd>
				<label>{$reference}</label>
			</dd>
		</dl>
		
		{if CASH_TRANSFER_SHOW_BANK}
			<dl>
				<dt>{lang}cash.claim.user.pay.transfer.data{/lang}</dt>
				<dd> </dd>
			</dl>
			<dl class="plain dataList">
				{if CASH_TRANSFER_OWNER}
					<dt>{lang}cash.bank.owner{/lang}</dt>
					<dd>{CASH_TRANSFER_OWNER}</dd>
				{/if}
				{if CASH_TRANSFER_BANK}
					<dt>{lang}cash.bank.bank{/lang}</dt>
					<dd>{CASH_TRANSFER_BANK}</dd>
				{/if}
				{if CASH_TRANSFER_CODE}
					<dt>{lang}cash.bank.code{/lang}</dt>
					<dd>{CASH_TRANSFER_CODE}</dd>
				{/if}
				{if CASH_TRANSFER_ACCOUNT}
					<dt>{lang}cash.bank.account{/lang}</dt>
					<dd>{CASH_TRANSFER_ACCOUNT}</dd>
				{/if}
				{if CASH_TRANSFER_IBAN}
					<dt>{lang}cash.bank.iban{/lang}</dt>
					<dd>{CASH_TRANSFER_IBAN}</dd>
				{/if}
				{if CASH_TRANSFER_BIC}
					<dt>{lang}cash.bank.bic{/lang}</dt>
					<dd>{CASH_TRANSFER_BIC}</dd>
				{/if}
			</dl>
		{/if}
	</section>
{/if}

{if $buttons|count || (!$buttons|count && !$allowTransfer)}
	<section class="section jsPaymentMethod">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}cash.claim.user.pay.method{/lang}</h2>
		</header>
		
		{if $buttons|count}
			<ul class="buttonList">
				{foreach from=$buttons item=button}
					<li>{@$button}</li>
				{/foreach}
			</ul>
		{else}
			<p>{lang}cash.claim.user.pay.method.none{/lang}</p>
		{/if}
	</section>
{/if}

<div class="formSubmit">
	<button class="button jsSubmitButton">{lang}wcf.global.button.submit{/lang}</button>
	<button class="buttonPrimary jsCancelButton">{lang}wcf.global.button.cancel{/lang}</button>
	
</div>