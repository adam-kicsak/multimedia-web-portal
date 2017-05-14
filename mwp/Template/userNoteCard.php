<div>
    <?php echo $userNote->type; ?>
    <p><span><?=$userNote->type == 'warn' ? 'Figyelmeztetés:' : ($userNote->type == 'ban' ? 'Kitiltás:' : 'Megjegyzés:') ?></span> <?=$userNote->author_id ?>, <?=$userNote->created ?>
    <p><span>Oka:</span> <?=$userNote->reason ?></p>
<?php if(empty($userNote->expire)) { ?>
    <p><span>Nem jár le soha</span></p>
<?php } else { ?>
    <p><span>Lejár:</span> <?=$userNote->expire ?></p>
<?php } ?>
</div>