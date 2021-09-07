<?php
/*
    Plugin Name: Primetime Pokédex Plugin
    description: Pokédex
    Author: Lucas M. Shepherd <lucas@leoblack.com>
    Version: 1.0.0
*/

$pokemonCount = get_pokecount();

class PrimetimePokedex {
    function __construct($count) {
        add_action('admin_menu', function() use ($count) { page_builder_menu($count); });
    }
    public function hooks() {
        add_action('init', 'add_pokedex_post_tax');
        add_action('admin_enqueue_scripts', 'pokedex_custom_js');
        add_action('wp_enqueue_scripts', 'pokedex_awesome_icons');
        add_action('wp_head', 'pokedex_custom');
        add_filter( 'single_template', 'set_pokedex_single_template' );
    }
}
$var = new PrimetimePokedex($pokemonCount);
add_action( 'plugins_loaded', array( $var, 'hooks' ) );

// ADMIN MENU AND PAGES
// Add custom taxonomy for Pokémon
function add_pokedex_post_tax() {
    $supports = array(
        'title', // post title
        'editor', // post content
        'author', // post author
        'thumbnail', // featured images
        'custom-fields', // custom fields
        'revisions', // post revisions
    );
    $labels = array(
        'name' => _x('Pokédex', 'plural'),
        'singular_name' => _x('Pokédex', 'singular'),
        'menu_name' => _x('Pokédex', 'admin menu'),
        'name_admin_bar' => _x('Pokédex', 'admin bar'),
        'add_new' => _x('Add New', 'add new'),
        'add_new_item' => __('Add New Pokémon'),
        'new_item' => __('New Pokémon'),
        'edit_item' => __('Edit Pokémon'),
        'view_item' => __('View Pokémon'),
        'all_items' => __('All Pokémon'),
        'search_items' => __('Search Pokédex'),
        'not_found' => __('No Pokémon found.'),
    );
    $args = array(
        'supports' => $supports,
        'labels' => $labels,
        'public' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'pokedex'),
        'has_archive' => true,
        'hierarchical' => false,
        'menu_icon' => 'data:image/svg+xml;base64,' . base64_encode('<svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><defs><style>.cls-1{fill:#fff;}</style></defs><path class="cls-1" d="M331.24,275.71a77.81,77.81,0,0,1-150.48,0q-65.26-2.67-128.59-10.09c5,108.09,94.38,194.17,203.82,194.17s198.85-86.15,203.84-194.29Q396.59,273,331.24,275.71ZM256,178.3a77.82,77.82,0,0,1,74.37,54.84c43.13-1.82,85.59-5.21,127-10.11a204.13,204.13,0,0,0-402.82.11c41.46,4.89,83.9,8.25,127,10A77.8,77.8,0,0,1,256,178.3Z"/><path class="cls-1" d="M289,256a32.87,32.87,0,0,1-7.63,21.1h0A33,33,0,1,1,289,256Z"/></svg>'),
        'taxonomies' => array('pokemon'),
    );
    register_post_type('pokedex', $args);
}
// Page builder menu item
function page_builder_menu($count){
    $count = $count;
    add_submenu_page('edit.php?post_type=pokedex', 'Page Builder', 'Page Builder', 'manage_options', 'page-builder', function() use ($count) { page_builder_page($count); });
}
// Page builder loop
function pokedex_button_clicked($id, $buildCount, $count) {
    for ($x = $id; $x <= $buildCount; $x++) { add_pokemon( $x, 'pokedex', $count ); }
}
// Page builder admin page
function page_builder_page($count) {
    $count = $count;
    $buildCount = $count;
    // Terminate if no access
    if (!current_user_can('manage_options'))  {
        wp_die( __('You do not have sufficient access to view this page.')    );
    }
    // If build/upgrade button was pressed
    if (isset($_POST['builder_button'])) {
        $buildCount = $_POST['builder_page_count'];
        $id = $_POST['builder_page_id'];
        echo "<br/><i>Building/updating up to page #$buildCount starting on page #$id.</i><br/><br/>";
        pokedex_button_clicked($id, $buildCount, $count);
    }
    // If delete all pages button was pressed
    if (isset($_POST['delete_button'])) {
        $allposts= get_posts( array('post_type'=>'pokedex','numberposts'=>-1) );
        foreach ($allposts as $eachpost) {
            wp_delete_post( $eachpost->ID, true );
        }
    } ?>
    <div class="wrap">
        <h2>Pok&eacute;dex Page Builder</h2>
        <form id="sendform" action="edit.php?post_type=pokedex&page=page-builder" method="post">
            <input type="hidden" value="true" name="builder_button" /><br/><br/>
            <label style='display: inline-block; width: 200px;'><b>From Page:</b></label> &nbsp;<input class='bpid' type='number' value='1' max='<?php echo $count; ?>' min='1' name='builder_page_id' /><br/><br/>
            <label style='display: inline-block; width: 200px;'><b>To Page:</b></label> &nbsp;<input class='bpid' type='number' value='<?php echo $count; ?>' max='<?php echo $count; ?>' min='1' name='builder_page_count' /><br/><br/>
            <small><i><b>Note</b>: Building more than ~200 pages at a time will cause a timeout and you will need to do this again starting from whatever page it timed out on.</i></small><br/><br/>
            <?php submit_button('Build/Update Pages', 'primary', 'submit', false); ?>
        </form><br/><br/><hr/><br/><br/>
        <form id="sendform" action="edit.php?post_type=pokedex&page=page-builder" method="post">
            <input type="hidden" value="true" name="delete_button" />
            <?php submit_button('Delete All Pages', 'secondary', 'submit', false); ?>
        </form>
    </div> <?php 
}
// Force template for 'pokedex' custom post type
function set_pokedex_single_template( $single_template ) {
    global $post;
    if ( 'pokedex' === $post->post_type ) { $single_template = dirname( __FILE__ ) . '/templates/single-pokedex.php'; }
    return $single_template;
}

// ENQUEUE
// Add custom scripts and styles to pokedex pages
function pokedex_custom_js() { 
    wp_enqueue_script( 'pokedex_scripts', plugin_dir_url( __FILE__ ) . 'dist/js/primetime-pokedex.js', array('jquery'), '1.0' );
}
function pokedex_custom(){
    if( is_singular('pokedex') ){
        wp_enqueue_style('pokedex_custom_css', plugins_url("/dist/css/pokedex.css", __FILE__));
    }
}
function pokedex_awesome_icons(){
    if( is_singular('pokedex') ){
        wp_enqueue_script('pokedex_icons', 'https://kit.fontawesome.com/4302ca1eeb.js');
        wp_enqueue_script('pokedex_custom_js', plugins_url('/primetime-pokedex/dist/js/primetime-pokedex.js'), array('jquery'), null, true);
    }
}

// UTILITY
// Convert inches to feet and inches (0'0")
function in_feet($in) {
    $feet = intval($in/12);
    $inches = $in%12;
    return sprintf("%d' %d''", $feet, $inches);
}
// Add leading zeros up to ($length) total digits
function add_digits($input, $length) {
    $input = substr(str_repeat(0, $length).$input, - $length);
    return $input;
}

// GET_POKEDATA
// Pull data from https://pokeapi.co/
function get_pokedata($value, $category = 'pokemon', $url = false) {
    $value = $value;
    $category = $category;
    $url = $url;
    if ($url == false) { $url = "https://pokeapi.co/api/v2/$category/$value"; } 
    else { $url = $value; }
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));
    $output = curl_exec($curl);
    curl_close($curl);
    $output = json_decode($output, true);
    return $output;
}

// GET_POKECOUNT
// Determine number of overall Pokémon
function get_pokecount() {
    $url = "https://pokeapi.co/api/v2/pokemon/?offset=0&limit=10000";
    $countArray = get_pokedata($url,'',true);
    $countArray = $countArray['results'];
    $count = 0;
    foreach($countArray as $pokemon) {
        $url = $pokemon['url'];
        $url = explode("/", $url);
        $url = $url[6];
        if($url > 9999) { return $count; }
        $count++;
    }
}

// ADD_POKEMON
// Add a page/pokemon to pokedex
function add_pokemon($id, $tax = 'pokedex', $count ) {
    $pokeCount = $count;
    $length = 3; // Bulbasaur #3 -> Bulbasaur #003
    $id = $id;
    $pokeID = $id;
    $pokeNextID = $pokeID + 1;
    $pokePrevID = $pokeID - 1;
    if($pokeNextID > $pokeCount) { $pokeNextID = 1; }
    if($pokePrevID < 1) { $pokePrevID = $pokeCount; }
    $dreamWorld = true;
    $pokeTypeList = array();
    $pokeWeakList = array();
    $pokeStrongList = array();
    $pokeSpellsList = array();
    $pokeSpellsNameList = array();
    $pokeHiddenSpellsList = array();
    $pokeHiddenSpellsNameList = array();
    $pokeEvolList = array();
    $pokeEvolClassList = array();
    $pokeEvolImageList = array();
    $pokeEvolIDList = array();
    $pokeDescList = array();
    $hasEvolution = false;
    $poke = get_pokedata($pokeID); //// pull pokemon details
    $pokeName = ucwords($poke['species']['name']); // name
    $pokeImage = $poke['sprites']['other']['dream_world']['front_default']; // image
    if(empty($pokeImage) || !$dreamWorld ) { $pokeImage = $poke['sprites']['other']['official-artwork']['front_default']; }
    $pokeStats = $poke['stats']; // stats
    $pokeType = $poke['types']; // type
    $pokeNextTitle = '';
    $pokeNextImage = '';
    $pokeNextClass = '';
    $pokePrevTitle = '';
    $pokePrevImage = '';
    $pokePrevClass = '';
    $pokeHabitat = '';
    $pokeGrowthRate = '';
    $pokeBaby = '';
    $pokeLegendary = '';
    $pokeMythical = '';
    $pokeNameAlt = '';
    $pokeStop = false;
    // Build lists
    foreach ($pokeType as $option) {
        // Types
        $typeName = $option['type']['name'];
        $pokeWeak = get_pokedata($typeName, 'type');
        // Strengths
        $pokeStrong = $pokeWeak['damage_relations']['half_damage_from'];
        // Weaknesses
        $pokeWeak = $pokeWeak['damage_relations']['double_damage_from'];
        // Build list of weaknesses (double damage) taken for this type
        foreach ($pokeWeak as $option) {
            $weakName = $option['name'];
            $weakName = ucwords($weakName);
            if(!in_array($weakName, $pokeWeakList)) {
                array_push($pokeWeakList, $weakName);
            }
        }
        // Build list of strengths (half damage) taken for this type
        foreach ($pokeStrong as $option) {
            $strongName = $option['name'];
            $strongName = ucwords($strongName);
            if(!in_array($strongName, $pokeStrongList)) {
                array_push($pokeStrongList, $strongName);
            }
        }
        $typeName = ucwords($typeName);
        array_push($pokeTypeList, $typeName); // types
    }
    sort($pokeTypeList);
    // Negate conflicting weakness/strength for multi-type pokemon
    foreach ($pokeStrongList as $option) {
        if( in_array($option, $pokeWeakList) ) {
            $key = array_search($option, $pokeWeakList);
            unset($pokeWeakList[$key]);
        }
    }
    sort($pokeWeakList);
    // Pokémon Details
    // Height
    $pokeHeight = $poke['height'];
    $pokeHeight = ($pokeHeight * .1) * 39.3701;
    $pokeHeight = round($pokeHeight, 0);
    $pokeHeight = in_feet($pokeHeight);
    // Weight
    $pokeWeight = $poke['weight'];
    $pokeWeight = ($pokeWeight * .1) / 0.453592;
    $pokeWeight = round($pokeWeight, 1);
    $pokeWeight = $pokeWeight . " lbs.";
    // Abilities
    $pokeSpells = $poke['abilities'];
    foreach($pokeSpells as $option) {
        $spellName = $option['ability']['name'];
        $spellURL = $option['ability']['url'];
        $spellURL = get_pokedata($spellURL,'',true);
        $spellList = $spellURL['effect_entries'];
        if(empty($spellList)) { $spellList = $spellURL['flavor_text_entries']; }
        foreach($spellList as $entry) {
            if($entry['language']['name'] == 'en') {
                if(!empty($entry['effect'])) { $spellDesc = $entry['effect']; } 
                else { $spellDesc = $entry['flavor_text']; }
                //$spellShortDesc = $entry['short_effect'];
            }
        }
        $hideSpell = $option['is_hidden'];
        if(!$hideSpell) { 
            $pokeSpellsList[$spellName] = $spellDesc; 
            array_push($pokeSpellsNameList, $spellName);
        }
        else { 
            $pokeHiddenSpellsList[$spellName] = $spellDesc; 
            array_push($pokeHiddenSpellsNameList, $spellName);
        }
    }
    // Pokémon Species
    $pokeSpec = get_pokedata($pokeID, 'pokemon-species');
    // Description
    $pokeDesc = $pokeSpec['flavor_text_entries'];
    $pokeDesc = array_reverse($pokeDesc);
    foreach($pokeDesc as $flavor) {
        if($flavor['language']['name'] == 'en') {
            $key = $flavor['version']['name'];
            if($key == 'sword' || $key == 'shield') {
                $pokeDescList[$key] = $flavor['flavor_text'];
            } elseif($pokeStop == false) {
                $pokeDescList['default'] = $flavor['flavor_text'];
                $pokeStop = true;
            }
        }
    }
    // Habitat
    if(!empty($pokeSpec['habitat'])) {
        $pokeHabitat = $pokeSpec['habitat']['name'];
        $pokeHabitatURL = $pokeSpec['habitat']['url'];
        $pokeHabitat = ucwords($pokeHabitat);
    }
    // Growth rate
    $pokeGrowthRate = $pokeSpec['growth_rate']['name'];
    $pokeGrowthRate = ucwords($pokeGrowthRate);
    $pokeGrowthRateURL = $pokeSpec['growth_rate']['url'];
    // Alt names
    $pokeNames = $pokeSpec['names'];
    foreach($pokeNames as $name) {
        if($name['language']['name'] == 'ja') { $pokeNameAlt = $name['name']; }
    }
    // Baby check
    $pokeBaby =  $pokeSpec['is_baby'];
    // Legendary check
    $pokeLegendary =  $pokeSpec['is_legendary'];
    // Mythical check
    $pokeMythical =  $pokeSpec['is_mythical'];
    // Gender
    $pokeGender = $pokeSpec['gender_rate'];
    if($pokeGender == 8) { $pokeGender = "Male";} 
    elseif($pokeGender == 0) { $pokeGender = "Female"; } 
    else { $pokeGender = "Male or Female"; }
    // Category
    $pokeCategory = $pokeSpec['genera'][7]['genus'];
    // Hatch Counter
    $pokeHatch = $pokeSpec['hatch_counter'];
    // Navigation
    // Next
    $pokeNext = get_pokedata($pokeNextID);
    $pokeNextName =  $pokeNext['name'];
    $pokeNextImage = $pokeNext['sprites']['other']['dream_world']['front_default'];
    if(!$pokeNextImage || !$dreamWorld ) { $pokeNextImage = $pokeNext['sprites']['other']['official-artwork']['front_default']; }
    $pokeNextTitle = ucwords("#$pokeNextID $pokeNextName"); // combine ids and names for titles
    $pokeNextClass = sanitize_title($pokeNextTitle);
    // Previous
    $pokePrev = get_pokedata($pokePrevID);
    $pokePrevName =  $pokePrev['name'];
    $pokePrevImage = $pokePrev['sprites']['other']['dream_world']['front_default'];
    if(!$pokePrevImage || !$dreamWorld ) { $pokePrevImage = $pokePrev['sprites']['other']['official-artwork']['front_default']; }
    $pokePrevTitle = ucwords("#$pokePrevID $pokePrevName");
    $pokePrevClass = sanitize_title($pokePrevTitle);
    // Evolution Chain
    $pokeEvolChainURL = $pokeSpec['evolution_chain']['url'];
    $pokeEvolChain = get_pokedata($pokeEvolChainURL,'',true);
    $pokeEvolChain = $pokeEvolChain['chain'];
    $pokeEvolSpeciesURL = $pokeEvolChain['species']['url'];
    $pokeEvolSpecies = get_pokedata($pokeEvolSpeciesURL,'',true);
    $pokeEvolSpeciesID = $pokeEvolSpecies['id'];
    $pokeEvol = get_pokedata($pokeEvolSpeciesID);
    $pokeEvolName = $pokeEvol['species']['name'];
    $pokeEvolTitle = ucwords("#$pokeEvolSpeciesID $pokeEvolName");
    $pokeEvolClass = sanitize_title($pokeEvolTitle);
    $pokeEvolSprites = $pokeEvol['sprites'];
    if(!empty($pokeEvolSprites['other']['dream_world']['front_default']) && $dreamWorld == true) {
        $pokeEvolImage = $pokeEvolSprites['other']['dream_world']['front_default'];
    } else { 
        $pokeEvolImage = $pokeEvolSprites['other']['official-artwork']['front_default']; 
    }
    array_push($pokeEvolIDList, $pokeEvolSpeciesID);
    array_push($pokeEvolImageList, $pokeEvolImage);
    array_push($pokeEvolList, $pokeEvolTitle);
    array_push($pokeEvolClassList, $pokeEvolClass);
    $pokeEvolChain = $pokeEvolChain['evolves_to'];
    if(!empty($pokeEvolChain)) { $hasEvolution = true; }
    while($hasEvolution == true) {
        $pokeEvolChain = $pokeEvolChain[0];
        $pokeEvolSpeciesURL = $pokeEvolChain['species']['url'];
        $pokeEvolSpecies = get_pokedata($pokeEvolSpeciesURL,'',true);
        $pokeEvolSpeciesID = $pokeEvolSpecies['id'];
        $pokeEvol = get_pokedata($pokeEvolSpeciesID);
        $pokeEvolName = $pokeEvol['species']['name'];
        $pokeEvolTitle = ucwords("#$pokeEvolSpeciesID $pokeEvolName");
        $pokeEvolClass = sanitize_title($pokeEvolTitle);
        $pokeEvolSprites = $pokeEvol['sprites'];
        if(!empty($pokeEvolSprites['other']['dream_world']['front_default']) && $dreamWorld == true) {
            $pokeEvolImage = $pokeEvolSprites['other']['dream_world']['front_default'];
        } else { 
            $pokeEvolImage = $pokeEvolSprites['other']['official-artwork']['front_default']; 
        }
        array_push($pokeEvolIDList, $pokeEvolSpeciesID);
        array_push($pokeEvolImageList, $pokeEvolImage);
        array_push($pokeEvolList, $pokeEvolTitle);
        array_push($pokeEvolClassList, $pokeEvolClass);
        $pokeEvolChain = $pokeEvolChain['evolves_to'];
        if(empty($pokeEvolChain)) { $hasEvolution = false; }
    }
    // Create post/page
    $pokeLongID = add_digits($pokeID, $length);
    $postTitle = "#$pokeLongID $pokeName";
    $postTitleClass = "#$pokeID $pokeName";
    $postTitleClass = sanitize_title($postTitleClass);
    $array = array(
        'post_title'    => "$postTitle",
        'post_type'     => "$tax",
        'post_status'   => 'publish',
        'post_name'     => "$postTitleClass",
        'post_author'   => 1,
        'comment_status'=> 'closed',
        'ping_status'   => 'closed'
    );
    // Check if post already exists and update by adding ID to array
    if( post_exists("$postTitle", '', '', "$tax") ) {
        $subarray = array(
            'fields'        => 'ids',
            'numberposts'   => 1,
            'name'          => "$postTitleClass",
            'post_type'     => "$tax",
            'title'         => "$postTitle",
        );
        $found = get_posts($subarray);
        $array = array_reverse($array);
        $array['ID'] = $found[0];
        $array = array_reverse($array);
        echo "<b>Page found</b>....";
        $post_id = wp_update_post( $array );
        $outMsg = "Page #$pokeID updated successfully.<br/>";
    } else {
        $post_id = wp_insert_post( $array );
        $outMsg = "Page #$pokeID created successfully.<br/>";
    }
    // Metadata
    pokepush($post_id, 'pokemon_id', $pokeID);
    pokepush($post_id, 'pokemon_name', $pokeName);
    pokepush($post_id, 'pokemon_image', $pokeImage);
    pokepush($post_id, 'pokemon_height', $pokeHeight);
    pokepush($post_id, 'pokemon_weight', $pokeWeight);
    pokepush($post_id, 'pokemon_gender', $pokeGender);
    pokepush($post_id, 'pokemon_category', $pokeCategory);
    foreach ($pokeStats as $option) {
        $statName = $option['stat']['name'];
        $statValue = $option['base_stat'];
        $statSlug = "pokemon_stat_$statName";
        pokepush($post_id, $statSlug, $statValue);
    }
    $pokeTypeString = implode(",", $pokeTypeList);
    pokepush($post_id, 'pokemon_type', $pokeTypeString);
    $pokeWeakString = implode(",", $pokeWeakList);
    pokepush($post_id, 'pokemon_weakness', $pokeWeakString);
    $pokeSpellsNameString = implode(",", $pokeSpellsNameList);
    pokepush($post_id, 'pokemon_abilities', $pokeSpellsNameString);
    foreach($pokeSpellsList as $spell => $desc) {
        $metaFieldName = 'pokemon_ability_' . $spell;
        pokepush($post_id, $metaFieldName, $desc);
    }
    $pokeHiddenSpellsNameString = implode(",", $pokeHiddenSpellsNameList);
    pokepush($post_id, 'pokemon_abilities_hidden', $pokeHiddenSpellsNameString);
    foreach($pokeHiddenSpellsList as $spell => $desc) {
        $metaFieldName = 'pokemon_ability_hidden_' . $spell;
        pokepush($post_id, $metaFieldName, $desc);
    }
    $pokeEvolString = implode(",", $pokeEvolList);
    pokepush($post_id, 'pokemon_evolution', $pokeEvolString);
    $pokeEvolClassString = implode(",", $pokeEvolClassList);
    pokepush($post_id, 'pokemon_evolution_class', $pokeEvolClassString);
    $pokeEvolImageString = implode(",", $pokeEvolImageList);
    pokepush($post_id, 'pokemon_evolution_image', $pokeEvolImageString);
    $pokeEvolIDString = implode(",", $pokeEvolIDList);
    pokepush($post_id, 'pokemon_evolution_id', $pokeEvolIDString);
    pokepush($post_id, 'pokemon_next', $pokeNextTitle);
    pokepush($post_id, 'pokemon_next_image', $pokeNextImage);
    pokepush($post_id, 'pokemon_next_class', $pokeNextClass);
    pokepush($post_id, 'pokemon_prev', $pokePrevTitle);
    pokepush($post_id, 'pokemon_prev_image', $pokePrevImage);
    pokepush($post_id, 'pokemon_prev_class', $pokePrevClass);
    pokepush($post_id, 'pokemon_habitat', $pokeHabitat);
    pokepush($post_id, 'pokemon_growth_rate', $pokeGrowthRate);
    pokepush($post_id, 'pokemon_is_baby', $pokeBaby);
    pokepush($post_id, 'pokemon_is_legendary', $pokeLegendary);
    pokepush($post_id, 'pokemon_is_mythical', $pokeMythical);
    pokepush($post_id, 'pokemon_name_alt', $pokeNameAlt);
    foreach($pokeDescList as $flavor => $desc) {
        $metaFieldName = 'pokemon_desc_' . $flavor;
        pokepush($post_id, $metaFieldName, $desc);
    }
    pokepush($post_id, 'pokemon_hatch_time', $pokeHatch);
    // Notification
    echo $outMsg;
}
// END ADD_POKEMON

// POKEPUSH
// Add/update custom fields on post
function pokepush($post_id, $meta, $pokeData) {
    $pokeData = $pokeData;
    $meta = $meta;
    if(!empty($pokeData)) {
        if(metadata_exists( 'post', $post_id, $meta )) { update_post_meta( $post_id, $meta, $pokeData ); } 
        else { add_post_meta( $post_id, $meta, $pokeData, true ); }
    } else {
        if(metadata_exists( 'post', $post_id, $meta )) { delete_post_meta( $post_id, $meta, $pokeData ); } 
    }
}