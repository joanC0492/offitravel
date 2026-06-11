<?php if(has_post_format('audio') && wp_oembed_get(get_post_meta(get_the_id(), "ova_met_embed_media", true))){
        echo wp_oembed_get(get_post_meta(get_the_id(), "ova_met_embed_media", true));
}