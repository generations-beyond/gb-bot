<?php include("css/team-metabox.css"); ?>
<div class="team-details-wrap">
    <div class="flex-row name"> 
        <label for="_<?=$post_type?>-name">Name</label>
        <div class="input-fields-wrap name">
            <div class="field-wrap first">
                <label for="_<?=$post_type?>-name-first">First</label>
                <input required type="text" id="field-name-first" name="_<?=$post_type?>-name-first"  value="<?=$team_name_first?>">
            </div>
            <div class="field-wrap middle">
                <label for="_<?=$post_type?>-name-middle">Middle</label>
                <input type="text" id="field-name-middle" name="_<?=$post_type?>-name-middle"  value="<?=$team_name_middle?>">
            </div>
            <div class="field-wrap last">
                <label for="_<?=$post_type?>-name-last">Last</label>
                <input required type="text" id="field-name-last" name="_<?=$post_type?>-name-last"  value="<?=$team_name_last?>">
            </div>
        </div>
    </div><!-- flex-row -->
    <div class="flex-row title">
        <div class="input-fields-wrap title">
            <div class="field-wrap title full">
                <label for="_<?=$post_type?>-title">Title</label>
                <input type="text" id="field-title" name="_<?=$post_type?>-title"  value="<?=$team_title?>">
            </div>
        </div>
    </div><!-- flex-row -->
    <div class="flex-row description">
        <div class="input-field-wrap description">
            <label for="_<?=$post_type?>-description">Description</label>
            <?=wp_editor( $team_description, '_'.$post_type.'-description', array() );?>
        </div>
    </div><!-- flex-row -->
    <div class="flex-row contact"> 
        <label for="_<?=$post_type?>-contact">Contact</label>
        <div class="input-fields-wrap contact">
            <div class="field-wrap phone">
                <label for="_<?=$post_type?>-contact-phone">Phone</label>
                <input type="text" id="field-contact-phone" name="_<?=$post_type?>-contact-phone"  value="<?=$team_contact_phone?>">
            </div>
            <div class="field-wrap extension">
                <label for="_<?=$post_type?>-contact-extension">Extension</label>
                <input type="text" id="field-contact-extension" name="_<?=$post_type?>-contact-extension"  value="<?=$team_contact_extension?>">
            </div>
            <div class="field-wrap email">
                <label for="_<?=$post_type?>-contact-email">Email</label>
                <input type="email" id="field-contact-email" name="_<?=$post_type?>-contact-email"  value="<?=$team_contact_email?>">
            </div>
        </div>
    </div><!-- flex-row -->
    <div class="flex-row social"> 
        <label for="_<?=$post_type?>-social">Social</label>
        <div class="input-fields-wrap social">
            <div class="field-wrap linkedin full">
                <label for="_<?=$post_type?>-social-linkedin">LinkedIn</label>
                <input type="url" id="field-social-linkedin" name="_<?=$post_type?>-social-linkedin"  value="<?=$team_social_linkedin?>">
            </div>
            <div class="field-wrap instagram full">
                <label for="_<?=$post_type?>-social-instagram">Instagram</label>
                <input type="url" id="field-social-instagram" name="_<?=$post_type?>-social-instagram"  value="<?=$team_social_instagram?>">
            </div>
            <div class="field-wrap twitter full">
                <label for="_<?=$post_type?>-social-twitter">Twitter</label>
                <input type="url" id="field-social-twitter" name="_<?=$post_type?>-social-twitter"  value="<?=$team_social_twitter?>">
            </div>
        </div>
    </div><!-- flex-row -->
</div>