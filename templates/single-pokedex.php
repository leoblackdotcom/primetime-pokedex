<?php
/**
 * @package storefront
 */

get_header(); 

function hokepoke($key) { 
	return get_post_meta( get_the_ID(), $key, true );
}

// Pokemon Details

// Incoming as basic string...
$category = 			hokepoke('pokemon_category');
$gender = 				hokepoke('pokemon_gender');
$height = 				hokepoke('pokemon_height');
$current_image = 		hokepoke('pokemon_image');
$current_id =			hokepoke('pokemon_id');
$current_name =			hokepoke('pokemon_name');
$current_name_alt =		hokepoke('pokemon_name_alt');
$next = 				hokepoke('pokemon_next');
$next_class = 			hokepoke('pokemon_next_class');
$next_image = 			hokepoke('pokemon_next_image');
$prev = 				hokepoke('pokemon_prev');
$prev_class = 			hokepoke('pokemon_prev_class');
$prev_image = 			hokepoke('pokemon_prev_image');
$stat_attack = 			hokepoke('pokemon_stat_attack');
$stat_defense = 		hokepoke('pokemon_stat_defense');
$stat_hp = 				hokepoke('pokemon_stat_hp');
$stat_sattack = 		hokepoke('pokemon_stat_special-attack');
$stat_sdefense = 		hokepoke('pokemon_stat_special-defense');
$stat_speed = 			hokepoke('pokemon_stat_speed');
$weight = 				hokepoke('pokemon_weight');
$desc_shield = 	    	hokepoke('pokemon_desc_shield');
$desc_sword = 	    	hokepoke('pokemon_desc_sword');
$desc = 				hokepoke('pokemon_desc_default');
$habitat = 				hokepoke('pokemon_habitat');
$growth_rate =	    	hokepoke('pokemon_growth_rate');
$is_baby =          	hokepoke('pokemon_is_baby');
$is_legendary =     	hokepoke('pokemon_is_legendary');
$is_mythical =      	hokepoke('pokemon_is_mythical');
$hatch_time =       	hokepoke('pokemon_hatch_time');

// Incoming as comma-seperated list...
$abilities = 			hokepoke('pokemon_abilities'); 
$abilities_hidden = 	hokepoke('pokemon_abilities_hidden');
$evolutions = 			hokepoke('pokemon_evolution');
$evolution_classes =	hokepoke('pokemon_evolution_class');
$evolution_images = 	hokepoke('pokemon_evolution_image');
$evolution_ids = 	    hokepoke('pokemon_evolution_id');
$types = 				hokepoke('pokemon_type');
$weaknesses = 			hokepoke('pokemon_weakness');
// ...convert lists to arrays...
$abilities =	 		explode(",", $abilities);
$abilities_hidden = 	explode(",", $abilities_hidden);
$chain = 				explode(",", $evolutions);
$chain_classes = 		explode(",", $evolution_classes);
$chain_images =	 		explode(",", $evolution_images);
$chain_ids =	 		explode(",", $evolution_ids);
$types = 				explode(",", $types);
$weaknesses = 			explode(",", $weaknesses);

// Set prev/next IDs
if($current_id == 898) { $next_id = 1; }
else { $next_id = $current_id + 1; }
if($current_id == 1) { $prev_id = 898; }
else { $prev_id = $current_id - 1; }

// POKESTATS
// Create stats block
function pokestats(	$atk, $def, $hp, $satk, $sdef, $spd ) {
	$stats = array();
	array_push($stats, $atk, $def, $hp, $satk, $sdef, $spd);
	echo '<ul class="clean-list">';
		for($i = 0; $i < count($stats); $i++) {
			if($i == 0) 	{ $stat_title = 'HP'; $stats_class = 'hp'; }
			elseif($i == 1) { $stat_title = 'Attack'; $stats_class = 'atk'; }
			elseif($i == 2) { $stat_title = 'Defense'; $stats_class = 'def'; }
			elseif($i == 3) { $stat_title = 'S-Atk'; $stats_class = 'satk'; }
			elseif($i == 4) { $stat_title = 'S-Def'; $stats_class = 'sdef'; }
			elseif($i == 5) { $stat_title = 'Speed'; $stats_class = 'spd'; }
			$stat = $stats[$i];
			$statPerc = 150 * ($stat / 255);

			echo '<li class="poke-stats-item">';
				echo "<div class='poke-stats-bar-fill'><span class='poke-stats-$stats_class' style='height:" . $statPerc . "%;'></span></div>";
				echo '<span class="poke-stats-item-amount">' . $stats[$i] . '</span>';
				echo '<span class="poke-stats-item-title">' . $stat_title . '</span>';
			echo '</li>';
		}
	echo '</ul>';
}

// POKETYPES
// Create types block
function poketypes( $types ) {
	$typeIcon = '';
	$typeColor = '';
	$typeColors = array();
	echo '<ul class="poke-pills clean-list">';
		foreach($types as $type) {
			$type = sanitize_title($type);
			if($type == 'bug') 		{ $typeIcon = 'pokemon-bug'; 		$typeColor = '#E8AA33'; }
			if($type == 'dark') 	{ $typeIcon = 'pokemon-dark'; 		$typeColor = '#D50A55'; }
			if($type == 'dragon') 	{ $typeIcon = 'pokemon-dragon'; 	$typeColor = '#4A4468'; }
			if($type == 'electric') { $typeIcon = 'pokemon-electric'; 	$typeColor = '#F1D941'; }
			if($type == 'fairy') 	{ $typeIcon = 'pokemon-fairy'; 		$typeColor = '#FE9EA0'; }
			if($type == 'fighting') { $typeIcon = 'pokemon-fighting'; 	$typeColor = '#F75B21'; }
			if($type == 'fire') 	{ $typeIcon = 'pokemon-fire'; 		$typeColor = '#DB2F2F'; }
			if($type == 'flying')	{ $typeIcon = 'pokemon-flying'; 	$typeColor = '#659BD2'; }
			if($type == 'ghost') 	{ $typeIcon = 'pokemon-ghost'; 		$typeColor = '#BE86C7'; }
			if($type == 'grass') 	{ $typeIcon = 'pokemon-grass'; 		$typeColor = '#78C850'; }
			if($type == 'ground') 	{ $typeIcon = 'pokemon-ground'; 	$typeColor = '#6F432A'; }
			if($type == 'ice') 		{ $typeIcon = 'pokemon-ice'; 		$typeColor = '#CCDCFF'; }
			if($type == 'normal')	{ $typeIcon = 'pokemon-normal'; 	$typeColor = '#A9A47A'; }
			if($type == 'poison') 	{ $typeIcon = 'pokemon-poison'; 	$typeColor = '#66D007'; }
			if($type == 'psychic') 	{ $typeIcon = 'pokemon-psychic';	$typeColor = '#D444A2'; }
			if($type == 'rock') 	{ $typeIcon = 'pokemon-rock'; 		$typeColor = '#87877b'; }
			if($type == 'steel') 	{ $typeIcon = 'pokemon-steel'; 		$typeColor = '#575964'; }
			if($type == 'water') 	{ $typeIcon = 'pokemon-water'; 		$typeColor = '#5D55BF'; }
			$typeClass = sanitize_title($type);
			$type = ucfirst($type);
			array_push($typeColors, $typeColor);
			echo "<li class='poke-types-item $typeClass' style='background-color: $typeColor;'>";
				echo "<div class='icon'><i class='fak fa-$typeIcon-circle'></i></div>";
				echo "<span>$type</span>";
			echo "</li>";
		}
	echo '</ul>';
	return $typeColors;
}

// POKEWEAKS
// Create weaknesses block
function pokeweaks( $weaknesses ) {
	echo '<ul class="poke-pills clean-list">';
		foreach($weaknesses as $weak) {
			$weakClass = sanitize_title($weak);
			$weak = ucfirst($weak);
			echo "<li class='poke-weaks-item $weakClass'>$weak</li>";
		}
	echo '</ul>';
}

// POKESPELLS
// Create abilities block
function pokespells($spells, $hidden = false) {
	foreach($spells as $spell) {
		$spellClass = sanitize_title($spell);
		//$spell = ucfirst($spell);
		$spell = implode('-', array_map('ucfirst', explode('-', $spell)));
		if($hidden) {
			$spellMeta = "pokemon_ability_hidden_" . $spellClass;
		} else {
			$spellMeta = "pokemon_ability_" . $spellClass;
		}
		$spellDesc = hokepoke($spellMeta);
		echo "<li class='poke-spells-item $spellClass'><h4>$spell</h4><p>$spellDesc</p></li>";
	} 
}

// POKECHAIN
// Create evolution chain block
function pokechain($chain, $chain_classes, $chain_images, $chain_ids, $current_id) {
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

// Set number for random button
$pokeRand = rand(1,898);

?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Karantina:wght@300;400;700&display=swap" rel="stylesheet">
<a name="Pokedex"></a>
<section id="pokedex">
	<div class="centered">
		<a href="../<?php echo $pokeRand ?>#Pokedex" class="button btn-small no-margin " rel="nofollow"><i class="fas fa-dice-three"></i>&nbsp; Random Pokémon</a>
	</div>
	<nav id="pokenav">
		<div class="pokenav-link pokenev-left">
			<a class="poke-nav-left" href="../<?php echo $prev_class; ?>" title="<?php echo $prev; ?>">
				<i class="fal fa-long-arrow-left arrow-button"></i>
				<span class="poke-nav-thumb" style="background-image: url('<?php echo $prev_image; ?>');"></span>
				<!--span class="poke-nav-name"><sup>#</sup><?php echo $prev_id; ?></span-->
			</a>
		</div>
		<div class="poke-current">
			<span class="poke-name"><sup>#</sup><?php echo $current_id; ?> <b><?php echo $current_name; ?></b></span>
		</div>
		<div class="pokenav-link pokenav-right">
			<a class="poke-nav-right" href="../<?php echo $next_class; ?>" title="<?php echo $next; ?>">
				<i class="fal fa-long-arrow-right arrow-button"></i>
				<span class="poke-nav-thumb" style="background-image: url('<?php echo $next_image; ?>');"></span>
				<!--span class="poke-nav-name"><sup>#</sup><?php echo $next_id; ?></span-->
			</a>
		</div>
	</nav>

	<article id="pokemon"
		class="pokemon-container <?php if($is_baby) : echo ' is-baby'; endif; if($is_legendary) : echo ' is-legendary'; endif; if($is_mythical) : echo ' is-mythical'; endif; ?>">
		<div class="poke-profile">
			<div class="poke-image">
				<div class="swap-button poke-flip">
					<!--i class="fas fa-sync-alt"></i--><i class="fas fa-ellipsis"></i></div>
				<div class="poke-image-description poke-flip">
					<div class="pid-section">
						<?php 
						//echo "<h3>Growth</h3>";
						echo '<ul class="poke-details-list clean-list">';
						if(!empty($hatch_time)) {
							$hatch_steps = $hatch_time * 255;
							echo "<li><span>Hatch Time</span><span>$hatch_time Cycles (~$hatch_steps Steps)</span></li>";
						}
						echo "<li><span>Growth Rate</span><span>$growth_rate</span></li>";
						echo "</ul>";
						?>
					</div>
					<div class="poke-stats">
						<?php pokestats($stat_hp, $stat_attack, $stat_defense, $stat_sattack, $stat_sdefense, $stat_speed); ?>
					</div>
				</div>
				<div class="poke-image-placeholder poke-flip">
					<div class="shine"></div>
					<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"
						xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 1000 1000"
						style="enable-background:new 0 0 1000 1000;" xml:space="preserve">
						<!-- Stroke ring -->
						<circle class="st0" cx="500" cy="500" r="302.8">
							<animateTransform attributeType="xml" attributeName="transform" type="rotate"
								from="0 500 500" to="360 500 500" dur="100s" repeatCount="indefinite" />
						</circle>
						<!-- Inner ring -->
						<circle class="st1" cx="500" cy="500" r="237.7">
							<animateTransform attributeType="xml" attributeName="transform" type="rotate"
								from="0 500 500" to="360 500 500" dur="40s" repeatCount="indefinite" />
						</circle>
						<!-- Outer ring -->
						<circle class="st2" cx="500" cy="500" r="366.8" transform="rotate(0 500 500)" ;>
							<animateTransform attributeType="xml" attributeName="transform" type="rotate"
								from="0 500 500" to="-360 500 500" dur="50s" repeatCount="indefinite" />
						</circle>
					</svg>
					<div class="poke-overview">
						<h1><?php echo $current_name; ?></h1>
						<span class="poke-cat"><?php echo $category; ?></span>
						<?php
							if($is_baby)      { echo "<span class='poke-add'>(Baby)</span>"; }
							if($is_legendary) { echo "<span class='poke-add'>(Legendary)</span>"; }
							if($is_mythical)  { echo "<span class='poke-add'>(Mythical)</span>"; }
						?>
						<span class="poke-id"><sup>#</sup><?php echo $current_id; ?></span>
					</div>
					<img src="<?php echo $current_image; ?>" alt="<?php echo $current_name; ?>" />
					<div class="poke-stats">
						<?php pokestats($stat_hp, $stat_attack, $stat_defense, $stat_sattack, $stat_sdefense, $stat_speed); ?>
					</div>
				</div>
				<div class="poke-card poke-chain">
					<h3>Evolutions</h3>
					<?php pokechain($chain, $chain_classes, $chain_images, $chain_ids, $current_id); ?>
				</div>
			</div>
			<div class="poke-summary">
				<div class="poke-card poke-card-clean poke-description">
					<h3>Pokémon Description</h3>
					<?php
						if($desc_sword) { echo "<p title='Sword'>$desc_sword</p>"; }
						if($desc_shield) { echo "<p title='Shield'>$desc_shield</p>"; }
						if(!$desc_sword && !$desc_shield) { echo "<p>$desc</p>"; }
					?>
				</div>
				<div class="poke-card poke-card-clean poke-types">
					<h3>Pokémon Type</h3>
					<?php 
						$typeColors = poketypes($types);
						if(count($typeColors) > 1) {
							$typeColors = implode(",", $typeColors);
							echo "<style>.poke-image-placeholder, .poke-image-description { background: linear-gradient(to right, $typeColors )!important; }</style>";
						} else {
							$typeColors = implode(",", $typeColors);
							echo "<style>.poke-image-placeholder, .poke-image-description { background: $typeColors!important; }</style>";
						}
					?>
				</div>
				<div class="poke-card poke-card-clean poke-weaks">
					<h3>Weaknesses</h3>
					<?php poketypes($weaknesses); ?>
				</div>
				<div class="poke-card poke-card-clean poke-details">
					<h3>Pokémon Details</h3>
					<ul class="poke-details-list clean-list">
						<li><span>Height</span><span><?php echo $height; ?></span></li>
						<li><span>Weight</span><span><?php echo $weight; ?></span></li>
						<li><span>Gender</span><span><?php echo $gender; ?></span></li>
						<?php if(!empty($habitat)) : ?>
						<li><span>Habitat</span><span><?php echo $habitat; ?></span></li>
						<?php endif; ?>
					</ul>
				</div>
				<?php if(!empty($abilities[0])): ?>
					<div class="poke-card poke-spells">
						<h3>Abilities</h3>
						<ul class="poke-spells-list clean-list">
							<li>
								<ul class="clean-list">
									<?php pokespells($abilities)?>
								</ul>
							</li>
						</ul>
					</div>
				<?php endif; ?>
				<?php if(!empty($abilities_hidden[0])): ?>
					<div class="poke-card poke-spells poke-spells-hidden">
						<h3>Hidden Abilities</h3>
						<ul class="poke-spells-list clean-list">
							<li>
								<ul class="clean-list">
									<?php pokespells($abilities_hidden, true); ?>
								</ul>
							</li>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</article>

</section>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php 
			if ( have_posts() ) { get_template_part( 'loop' ); } 
			else { get_template_part( 'content', 'none' ); }
		?>
	</main><!-- #main -->
</div><!-- #primary -->

<?php


//do_action( 'storefront_sidebar' );
get_footer();
