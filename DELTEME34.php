<?php 

function pokechain(array $chain) {
	$chainLength = count($chain);
	echo '<ul class="poke-chain flex-list clean-list">';
		for($i = 0; $i < $chainLength; $i++) {
			echo '<li class="poke-chain-link';
        if($chain_ids[$i] == $current_id ) {
          echo ' current';
        }
        echo '">';
			echo '<a href="../' . $chain_classes[$i] . '">';
				echo '<div class="poke-chain-link-image"><img src="' . $chain_images[$i] . '" /><i class="fas fa-chevron-double-right arrow-chain-icon"></i></div>';
				echo '<h5>' . $chain[$i] . '</h5>';
			echo '</a>';
		echo '</li>';
		}
	echo '</ul>';
}
