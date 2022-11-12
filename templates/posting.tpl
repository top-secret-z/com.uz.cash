{capture assign='pageTitle'}{lang}cash.posting.posting{/lang}{/capture}

{capture assign='contentTitle'}{lang}cash.posting.posting{/lang}{/capture}

{capture assign='contentHeaderNavigation'}
    <li><a href="{link application='cash' controller='PostingList'}{/link}" class="button buttonPrimary"><span class="icon icon16 fa-files-o"></span> <span>{lang}cash.posting.postings{/lang}</span></a></li>
{/capture}

{include file='header'}

{assign var='objectID' value=$posting->postingID}

<div id="overview" class="section">
    <section class="section">
        <h2 class="sectionTitle">{$posting->getTitle()}</h2>

        <p>{$posting->amount|currency} {$posting->currency}</p>
    </section>

    <div class="section">
        <div class="htmlContent">
            {@$posting->getFormattedMessage()}
        </div>
    </div>

    {include file='attachments'}
</div>

{include file='footer'}
