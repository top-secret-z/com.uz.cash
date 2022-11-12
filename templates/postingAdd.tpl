{capture assign='pageTitle'}{lang}cash.posting.{@$action}{/lang}{/capture}

{capture assign='contentTitle'}{lang}cash.posting.{@$action}{/lang}{/capture}

{capture assign='contentHeaderNavigation'}
    <li><a href="{link application='cash' controller='PostingList'}{/link}" class="button buttonPrimary"><span class="icon icon16 fa-files-o"></span> <span>{lang}cash.posting.list{/lang}</span></a></li>
{/capture}

{include file='header'}

{include file='formError'}

{if !$categoryWarning}
    <p class="error">{lang}cash.posting.add.category.warning{/lang}</p>
{else}
    <form id="messageContainer" class="jsFormGuard" method="post" action="{if $action == 'add'}{link application='cash' controller='PostingAdd'}{/link}{else}{link application='cash' controller='PostingEdit' id=$postingID}{/link}{/if}">
        <section class="section">
            <h2 class="sectionTitle">{lang}cash.posting.add.general{/lang}</h2>

            <dl{if $errorField == 'subject'} class="formError"{/if}>
                <dt><label for="subject">{lang}cash.posting.add.subject{/lang}</label></dt>
                <dd>
                    <input type="text" id="subject" name="subject" value="{$subject}" maxlength="255" class="long">
                    {if $errorField == 'subject'}
                        <small class="innerError">
                            {if $errorType == 'empty'}
                                {lang}wcf.global.form.error.empty{/lang}
                            {else}
                                {lang}cash.posting.add.subject.error.{@$errorType}{/lang}
                            {/if}
                        </small>
                    {/if}
                </dd>
            </dl>

            <dl>
                <dt>{lang}cash.posting.add.type{/lang}</dt>
                <dd>
                    <label><input type="radio" name="type" value="expense"{if $type == 'expense'} checked{/if}> {lang}cash.posting.add.type.expense{/lang}</label>
                    <label><input type="radio" name="type" value="income"{if $type == 'income'} checked{/if}> {lang}cash.posting.add.type.income{/lang}</label>
                </dd>
            </dl>

            <dl{if $errorField == 'categoryID'} class="formError"{/if}>
                <dt><label for="categoryID">{lang}cash.posting.add.categoryID{/lang}</label></dt>
                <dd>
                    <select id="categoryID" name="categoryID">
                        {include file='categoryOptionList'}
                    </select>

                    {if $errorField == 'categoryID'}
                        <small class="innerError">
                            {if $errorType == 'empty'}
                                {lang}wcf.global.form.error.empty{/lang}
                            {else}
                                {lang}cash.posting.add.categoryID.error.{@$errorType}{/lang}
                            {/if}
                        </small>
                    {/if}
                </dd>
            </dl>

            <dl{if $errorField == 'time'} class="formError"{/if}>
            <dt><label for="time">{lang}wcf.global.date{/lang}</label></dt>
            <dd>
                <input type="datetime" id="time" name="time" value="{$time}" class="medium">
                {if $errorField == 'time'}
                    <small class="innerError">
                        {if $errorType == 'empty'}
                            {lang}wcf.global.form.error.empty{/lang}
                        {else}
                            {lang}cash.posting.add.time.error.{@$errorType}{/lang}
                        {/if}
                    </small>
                {/if}
            </dd>
        </dl>
        </section>

        <section class="section">
            <h2 class="sectionTitle">{lang}cash.posting.add.amount{/lang}</h2>

            <dl {if $errorField == 'amount'} class="formError"{/if}>
                <dt><label for="amount">{lang}cash.posting.add.amount{/lang}</label></dt>
                <dd>
                    <input type="text" id="amount" name="amount" value="{@$amount|currency}" class="tiny">
                    {if $errorField == 'amount'}
                        <small class="innerError">
                            {if $errorType == 'empty'}
                                {lang}wcf.global.form.error.empty{/lang}
                            {else}
                                {lang}cash.posting.add.amount.error.{@$errorType}{/lang}
                            {/if}
                        </small>
                    {/if}
                </dd>
            </dl>

            <dl {if $errorField == 'currency'} class="formError"{/if}>
                <dt><label for="currency">{lang}cash.posting.add.currency{/lang}</label></dt>
                <dd>
                    <select name="currency" id="currency">
                        {htmlOptions output=$availableCurrencies values=$availableCurrencies selected=$currency}
                    </select>

                    {if $errorField == 'currency'}
                        <small class="innerError">
                            {if $errorType == 'empty'}
                                {lang}wcf.global.form.error.empty{/lang}
                            {else}
                                {lang}cash.posting.add.currency.error.{@$errorType}{/lang}
                            {/if}
                        </small>
                    {/if}
                </dd>
            </dl>
        </section>

        <section class="section">
            <h2 class="sectionTitle">{lang}cash.posting.add.message{/lang}</h2>

            <dl class="wide{if $errorField == 'text'} formError{/if}">
                <dt><label for="text">{lang}cash.posting.add.message{/lang}</label></dt>
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
                                {lang}cash.posting.add.message.error.{@$errorType}{/lang}
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
                {include file='messageFormPreviewButton' previewMessageObjectType='com.uz.cash.posting' previewMessageObjectID=$posting->postingID}
            {else}
                {include file='messageFormPreviewButton' previewMessageObjectType='com.uz.cash.posting' previewMessageObjectID=0}
            {/if}
            {csrfToken}
        </div>
    </form>
{/if}

<script data-relocate="true">
    $(function() {
        new WCF.Message.FormGuard();
    });
</script>

{include file='wysiwyg'}
{include file='footer'}
