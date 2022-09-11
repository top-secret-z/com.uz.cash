{capture assign='pageTitle'}{lang}cash.claim.claim{/lang}{/capture}

{capture assign='contentTitle'}{lang}cash.claim.claim{/lang}{/capture}

{capture assign='contentHeaderNavigation'}
	<li><a href="{link application='cash' controller='MyClaimList'}{/link}" class="button buttonPrimary"><span class="icon icon16 fa-files-o"></span> <span>{lang}cash.claim.myClaims{/lang}</span></a></li>
{/capture}

{include file='header'}

{assign var='objectID' value=$claim->claimID}

<div id="overview" class="section">
	<section class="section">
		<h2 class="sectionTitle">{$claim->getTitle()}</h2>
		
		<p>{$claim->amount|currency} {$claim->currency}</p>
	</section>
	
	<div class="section">
		<div class="htmlContent">
			{@$claim->getFormattedMessage()}
		</div>
	</div>
	
	{include file='attachments'}
</div>

{include file='footer'}
