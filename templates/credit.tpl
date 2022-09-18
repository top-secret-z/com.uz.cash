{capture assign='pageTitle'}{lang}cash.credit.credit{/lang}{/capture}

{capture assign='contentTitle'}{lang}cash.credit.credit{/lang}{/capture}

{capture assign='contentHeaderNavigation'}
    <li><a href="{link application='cash' controller='MyCreditList'}{/link}" class="button buttonPrimary"><span class="icon icon16 fa-files-o"></span> <span>{lang}cash.credit.myCredits{/lang}</span></a></li>
{/capture}

{include file='header'}

{assign var='objectID' value=$credit->creditID}

<div id="overview" class="section">
    <section class="section">
        <h2 class="sectionTitle">{$credit->getTitle()}</h2>

        <p>{$credit->amount|currency} {$credit->currency}</p>
    </section>

    <div class="section">
        <div class="htmlContent">
            {@$credit->getFormattedMessage()}
        </div>
    </div>

    {include file='attachments'}
</div>

{include file='footer'}
