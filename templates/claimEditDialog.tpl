<section class="section">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}cash.claim.user.data{/lang}</h2>
	</header>
	
	<dl>
		<dt><label for="amount">{lang}cash.claim.user.amount{/lang}</label></dt>
		<dd>
			<input type="number" id="amount" name="amount" class="small jsAmount" min="0" value="{$amount}" step=".01">
		</dd>
	</dl>
	
	<dl>
		<dt><label for="currency">{lang}cash.claim.user.currency{/lang}</label></dt>
		<dd>
			<select name="currency" id="currency" class="jsCurrency">
				{foreach from=$availableCurrencies item=available}
					<option value="{$available}"{if $currency == $available} selected="selected"{/if}>{$available}</option>
				{/foreach}
			</select>
		</dd>
	</dl>

	
	<dl>
		<dt><label for="subject">{lang}cash.claim.user.subject{/lang}</label></dt>
		<dd>
			<textarea id="subject" name="subject" class="long jsSubject" cols="40" rows="2">{$subject}</textarea>
		</dd>
	</dl>
	
	<div class="formSubmit">
		<button class="buttonPrimary jsSubmitButton">{lang}wcf.global.button.submit{/lang}</button>
		<button class="jsCancelButton">{lang}wcf.global.button.cancel{/lang}</button>
	</div>
</section>