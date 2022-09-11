{capture assign='pageTitle'}{lang}cash.export.export{/lang}{/capture}
{capture assign='contentTitle'}{lang}cash.export.export{/lang}{/capture}

{capture assign='contentHeaderNavigation'}
	<li><a href="{link application='cash' controller='Overview'}{/link}" class="button"><span class="icon icon16 fa-files-o"></span> <span>{lang}cash.cash.overview{/lang}</span></a></li>
{/capture}

{include file='header'}

{include file='formError'}

<form class="jsFormGuard" method="post" action="{link application='cash' controller='CashExport'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}cash.export.period{/lang}</h2>
		
		<dl>
			<dt></dt>
			<dd>
				<input type="date" id="startDate" name="startDate" value="{$startDate}" data-placeholder="{lang}wcf.date.period.start{/lang}">
			</dd>
		</dl>
		<dl>
			<dt></dt>
			<dd>
				<input type="date" id="endDate" name="endDate" value="{$endDate}" data-placeholder="{lang}wcf.date.period.end{/lang}">
			</dd>
		</dl>
	</section>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}cash.export.contents{/lang}</h2>
		
		<dl{if $errorField == 'contents'} class="formError"{/if}>
			<dt></dt>
			<dd>
				{foreach from=$availableContents key=$key item=$content}
					<div>
						<label>
							<label><input type="checkbox" name="selectedContents[{$key}]" value="1"{if $key|in_array:$selectedContents} checked="checked"{/if}> {$content}</label>
						</label>
					</div>
				{/foreach}
				
				{if $errorField == 'contents'}
					<small class="innerError">
						{lang}cash.export.contents.error.{@$errorType}{/lang}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" name="openClaims" value="1"{if $openClaims} checked{/if}> {lang}cash.export.openClaims{/lang}</label>
			</dd>
		</dl>
	</section>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}cash.export.currencies{/lang}</h2>
		
		<dl{if $errorField == 'currencies'} class="formError"{/if}>
			<dt></dt>
			<dd>
				{foreach from=$availableCurrencies key=$key item=$currency}
					<div>
						<label>
							<label><input type="checkbox" name="selectedCurrencies[{$key}]" value="1"{if $key|in_array:$selectedCurrencies} checked="checked"{/if}> {$currency}</label>
						</label>
					</div>
				{/foreach}
				
				{if $errorField == 'currencies'}
					<small class="innerError">
						{lang}cash.export.currencies.error.{@$errorType}{/lang}
					</small>
				{/if}
			</dd>
		</dl>
	</section>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}cash.export.categories{/lang}</h2>
		
		{if !$flexibleCategoryList|isset}{assign var=flexibleCategoryList value=$categoryList}{/if}
		{if !$flexibleCategoryListName|isset}{assign var=flexibleCategoryListName value='categoryIDs'}{/if}
		{if !$flexibleCategoryListID|isset}{assign var=flexibleCategoryListID value='flexibleCategoryList'}{/if}
		{if !$flexibleCategoryListSelectedIDs|isset}{assign var=flexibleCategoryListSelectedIDs value=$categoryIDs}{/if}
		<ol class="flexibleCategoryList" id="{$flexibleCategoryListID}">
			{foreach from=$flexibleCategoryList item=categoryItem}
				<li>
					<div class="containerHeadline">
						<h3><label{if $categoryItem->getDescription()} class="jsTooltip" title="{$categoryItem->getDescription()}"{/if}><input type="checkbox" name="{$flexibleCategoryListName}[]" value="{@$categoryItem->categoryID}" class="jsCategory"{if $categoryItem->categoryID|in_array:$flexibleCategoryListSelectedIDs} checked{/if}> {$categoryItem->getTitle()}</label></h3>
					</div>
					
					{if $categoryItem->hasChildren()}
						<ol>
							{foreach from=$categoryItem item=subCategoryItem}
								<li>
									<label{if $subCategoryItem->getDescription()} class="jsTooltip" title="{$subCategoryItem->getDescription()}"{/if} style="font-size: 1rem;"><input type="checkbox" name="{$flexibleCategoryListName}[]" value="{@$subCategoryItem->categoryID}" class="jsChildCategory"{if $subCategoryItem->categoryID|in_array:$flexibleCategoryListSelectedIDs} checked{/if}> {$subCategoryItem->getTitle()}</label>
									
									{if $subCategoryItem->hasChildren()}
										<ol>
											{foreach from=$subCategoryItem item=subSubCategoryItem}
												<li>
													<label{if $subSubCategoryItem->getDescription()} class="jsTooltip" title="{$subSubCategoryItem->getDescription()}"{/if}><input type="checkbox" name="{$flexibleCategoryListName}[]" value="{@$subSubCategoryItem->categoryID}" class="jsSubChildCategory"{if $subSubCategoryItem->categoryID|in_array:$flexibleCategoryListSelectedIDs} checked{/if}> {$subSubCategoryItem->getTitle()}</label>
												</li>
											{/foreach}
										</ol>
									{/if}
								</li>
							{/foreach}
						</ol>
					{/if}
				</li>
			{/foreach}
		</ol>
		
		{if $errorField == 'categoryIDs'}
			<small class="innerError">
				{if $errorType == 'empty'}
					{lang}wcf.global.form.error.empty{/lang}
				{else}
					{lang}cash.export.categories.error.{@$errorType}{/lang}
				{/if}
			</small>
		{/if}
		
		{event name='categoryFields'}
	</section>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
