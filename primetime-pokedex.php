<?php
/*
    Plugin Name: Primetime Pokédex Plugin
    description: Pokédex
    Author: Lucas M. Shepherd <lucas@leoblack.com>
    Version: 1.0.0
*/

class PrimetimePokedex {
    public function hooks() {
        add_action('init', 'add_pokedex_post_tax');
        add_action('admin_menu', 'page_builder_menu');
        add_action('admin_enqueue_scripts', 'pokedex_custom_js');

        // Convert inches to feet and inches (0'0")
        function inFeet($in) {
            $feet = intval($in/12);
            $inches = $in%12;
            return sprintf("%d' %d''", $feet, $inches);
        }

        // Add custom taxonomy for pokemon
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

        // Add menu items
        function page_builder_menu(){
            add_submenu_page('edit.php?post_type=pokedex', 'Page Builder', 'Page Builder', 'manage_options', 'page-builder', 'page_builder_page');
        }

        // Page builder loop
        function pokedex_button_clicked($id, $buildCount) {
            for ($x = $id; $x <= $buildCount; $x++) { add_pokemon( $x ); }
        }

        // Page builder admin page
        function page_builder_page() {
            if (!current_user_can('manage_options'))  {
                wp_die( __('You do not have sufficient pilchards to access this page.')    );
            }
            echo '<div class="wrap">';
            echo '<h2>Pokédex Page Builder</h2>';
            if ( isset($_POST['builder_button'])) {
                $buildCount = $_POST['builder_page_count'];
                $id = $_POST['builder_page_id'];
                echo "<i>Building/updating up to page #$buildCount starting on page #$id.</i><br/><br/>";
                pokedex_button_clicked($id, $buildCount);
            }
            echo '<form id="sendform" action="edit.php?post_type=pokedex&page=page-builder" method="post">';
            echo '<input type="hidden" value="true" name="builder_button" />';
            echo "<br/><br/><label style='display: inline-block; width: 200px;'><b>From Page:</b></label> &nbsp;<input class='bpid' type='number' value='1' name='builder_page_id' />";
            echo "<br/><br/><label style='display: inline-block; width: 200px;'><b>To Page:</b></label> &nbsp;<input class='bpid' type='number' value='898' name='builder_page_count' />";
            echo "<br/><br/><small><i><b>Note</b>: Building more than ~200 pages at a time will cause a timeout and you will need to do this again starting from whatever page it timed out on.</i></small><br/><br/>";
            submit_button('Build/Update Pages', 'primary', 'submit', false);
            echo '</form>';
            echo '</div>';
        };

        // Pull data from https://pokeapi.co/
        function get_pokedata($value, $category = 'pokemon', $url = false) {
            $value = $value;
            $category = $category;
            $url = $url;
            if ($url == false) { $url = "https://pokeapi.co/api/v2/$category/$value"; } 
            else { $url = $value; }
            // start curl
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
            // end curl
            $output = json_decode($output, true);
            return $output;
        }

        // Add/update custom fields on post
        function pokepush($post_id, $meta, $pokeName) {
            $pokeName = $pokeName;
            $meta = $meta;
            if(metadata_exists( 'post', $post_id, $meta )) {
                update_post_meta( $post_id, $meta, $pokeName );
            } else {
                add_post_meta( $post_id, $meta, $pokeName, true );
            };
        }

        function add_digits($input, $length) {
            $input = substr(str_repeat(0, $length).$input, - $length);
            return $input;
        }

        // Add a page/pokemon to pokedex
        function add_pokemon($id, $tax = 'pokedex', $count = 898 ) {
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
            $pokeHiddenSpellsList = array();
            $pokeEvolList = array();
            $pokeEvolClassList = array();
            $pokeEvolImageList = array();
            $hasEvolution = false;
            $poke = get_pokedata($pokeID); //// pull pokemon details
            $pokeName = ucfirst($poke['name']); // name
            $pokeImage = $poke['sprites']['other']['dream_world']['front_default']; // image
            if(empty($pokeImage) || !$dreamWorld ) { $pokeImage = $poke['sprites']['other']['official-artwork']['front_default']; }
            $pokeStats = $poke['stats']; // stats
            $pokeType = $poke['types']; // type
            foreach ($pokeType as $option) {
                $typeName = $option['type']['name'];
                array_push($pokeTypeList, $typeName); // types
                $pokeWeak = get_pokedata($typeName, 'type'); //// pull type
                $pokeStrong = $pokeWeak['damage_relations']['half_damage_from']; // strengths
                $pokeWeak = $pokeWeak['damage_relations']['double_damage_from']; // weaknesses
                // Build list of weaknesses (double damage) taken for this type
                foreach ($pokeWeak as $option) {
                    $weakName = $option['name'];
                    if(!in_array($weakName, $pokeWeakList)) {
                        array_push($pokeWeakList, $weakName);
                    }
                }
                // Build list of strengths (half damage) taken for this type
                foreach ($pokeStrong as $option) {
                    $strongName = $option['name'];
                    if(!in_array($strongName, $pokeStrongList)) {
                        array_push($pokeStrongList, $strongName);
                    }
                }
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
            $pokeHeight = $poke['height']; // height
            $pokeHeight = ($pokeHeight * .1) * 39.3701;
            $pokeHeight = round($pokeHeight, 0);
            $pokeHeight = inFeet($pokeHeight);
            $pokeWeight = $poke['weight']; // weight
            $pokeWeight = ($pokeWeight * .1) / 0.453592;
            $pokeWeight = round($pokeWeight, 1);
            $pokeWeight = $pokeWeight . " lbs.";
            $pokeSpec = get_pokedata($pokeID, 'pokemon-species'); //// pull species details
            $pokeGender = $pokeSpec['gender_rate']; // gender
            if($pokeGender == 8) { $pokeGender = "Male";} 
            elseif($pokeGender == 0) { $pokeGender = "Female"; } 
            else { $pokeGender = "Male or Female"; }
            $pokeCategory = $pokeSpec['genera'][7]['genus']; // category
            $pokeSpells = $poke['abilities']; // abilities
            foreach($pokeSpells as $option) {
                $spellName = $option['ability']['name'];
                $hideSpell = $option['is_hidden'];
                if(!$hideSpell) { array_push($pokeSpellsList, $spellName); }
                else { array_push($pokeHiddenSpellsList, $spellName); }
            }

            // POKEDEX PAGE NAVIGATION
            $pokeNext = get_pokedata($pokeNextID); // next
            $pokeNextName =  $pokeNext['name'];
            $pokeNextImage = $pokeNext['sprites']['other']['dream_world']['front_default'];
            if(!$pokeNextImage || !$dreamWorld ) { $pokeNextImage = $pokeNext['sprites']['other']['official-artwork']['front_default']; }
            $pokePrev = get_pokedata($pokePrevID); // previous
            $pokePrevName =  $pokePrev['name'];
            $pokePrevImage = $pokePrev['sprites']['other']['dream_world']['front_default'];
            if(!$pokePrevImage || !$dreamWorld ) { $pokePrevImage = $pokePrev['sprites']['other']['official-artwork']['front_default']; }
            $pokeNextID = add_digits($pokeNextID, $length);
            $pokePrevID = add_digits($pokePrevID, $length);
            $pokeNextTitle = ucfirst("#$pokeNextID $pokeNextName"); // combine ids and names for titles
            $pokeNextClass = sanitize_title($pokeNextTitle);
            $pokePrevTitle = ucfirst("#$pokePrevID $pokePrevName");
            $pokePrevClass = sanitize_title($pokePrevTitle);

            // POKEMON EVOLUTION CHAIN
            $pokeEvolChainURL = $pokeSpec['evolution_chain']['url']; // evolution chain url
            $pokeEvolChain = get_pokedata($pokeEvolChainURL,'',true); //// pull evolution details
            $pokeEvolChain = $pokeEvolChain['chain']; // evolution chain
            $pokeEvolSpeciesURL = $pokeEvolChain['species']['url'];
            $pokeEvolSpecies = get_pokedata($pokeEvolSpeciesURL,'',true);
            $pokeEvolSpeciesID = $pokeEvolSpecies['id'];
            $pokeEvol = get_pokedata($pokeEvolSpeciesID);
            $pokeEvolName = $pokeEvol['species']['name'];
            $pokeEvolID = add_digits($pokeEvolSpeciesID, $length); // #1 -> #001 if $length is equal to 3
            $pokeEvolTitle = "#$pokeEvolID $pokeEvolName";
            $pokeEvolClass = sanitize_title($pokeEvolTitle);
            $pokeEvolSprites = $pokeEvol['sprites'];
            if(!empty($pokeEvolSprites['other']['dream_world']['front_default']) && $dreamWorld == true) {
                $pokeEvolImage = $pokeEvolSprites['other']['dream_world']['front_default'];
            } else { 
                $pokeEvolImage = $pokeEvolSprites['other']['official-artwork']['front_default']; 
            }
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
                $pokeEvolID = add_digits($pokeEvolSpeciesID, $length); // #1 -> #001 if $length is equal to 3
                $pokeEvolTitle = "#$pokeEvolID $pokeEvolName";
                $pokeEvolClass = sanitize_title($pokeEvolTitle);
                $pokeEvolSprites = $pokeEvol['sprites'];
                if(!empty($pokeEvolSprites['other']['dream_world']['front_default']) && $dreamWorld == true) {
                    $pokeEvolImage = $pokeEvolSprites['other']['dream_world']['front_default'];
                } else { 
                    $pokeEvolImage = $pokeEvolSprites['other']['official-artwork']['front_default']; 
                }
                array_push($pokeEvolImageList, $pokeEvolImage);
                array_push($pokeEvolList, $pokeEvolTitle);
                array_push($pokeEvolClassList, $pokeEvolClass);
                $pokeEvolChain = $pokeEvolChain['evolves_to'];
                if(empty($pokeEvolChain)) { $hasEvolution = false; }
            }

            // Create post/page
            $postTitle = "#$pokeID $pokeName";
            $postTitleClass = sanitize_title($postTitle);
            $array = array(
                'post_title'    => "$postTitle",
                'post_type'     => "$tax",
                'post_status'   => 'publish',
                'post_name'     => "$postTitleClass",
                'post_author'   => 1,
                'comment_status'=> 'closed',
                'ping_status'   => 'closed'
            );
            // check if post already exists and update by adding ID to array
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
                echo "<p><b>Page found!</b></p>";
                $post_id = wp_update_post( $array );
                $outMsg = "Pokemon/Page #$pokeID updated.<br/><br/>";
            } else {
                $post_id = wp_insert_post( $array );
                $outMsg = "Pokemon/Page #$pokeID created successfully.<br/><br/>";
            }
            // update post meta fields
            pokepush($post_id, 'pokemon_name', $pokeName); // name
            pokepush($post_id, 'pokemon_image', $pokeImage); // image
            pokepush($post_id, 'pokemon_height', $pokeHeight); // height
            pokepush($post_id, 'pokemon_weight', $pokeWeight); // weight
            pokepush($post_id, 'pokemon_gender', $pokeGender); // gender
            pokepush($post_id, 'pokemon_category', $pokeCategory); // category
            foreach ($pokeStats as $option) {
                $statName = $option['stat']['name']; // stats
                $statValue = $option['base_stat'];
                $statSlug = "pokemon_stat_$statName";
                pokepush($post_id, $statSlug, $statValue);
            }
            $pokeTypeString = implode(",", $pokeTypeList); // type
            pokepush($post_id, 'pokemon_type', $pokeTypeString);
            $pokeWeakString = implode(",", $pokeWeakList); // weaknesses
            pokepush($post_id, 'pokemon_weakness', $pokeWeakString);
            $pokeSpellsString = implode(",", $pokeSpellsList); // abilities
            pokepush($post_id, 'pokemon_abilities', $pokeSpellsString);
            $pokeHiddenSpellsString = implode(",", $pokeHiddenSpellsList); // hidden abilities
            pokepush($post_id, 'pokemon_abilities_hidden', $pokeHiddenSpellsString);
            $pokeEvolString = implode(",", $pokeEvolList); // evolution
            pokepush($post_id, 'pokemon_evolution', $pokeEvolString);
            $pokeEvolClassString = implode(",", $pokeEvolClassList);
            pokepush($post_id, 'pokemon_evolution_class', $pokeEvolClassString);
            $pokeEvolImageString = implode(",", $pokeEvolImageList);
            pokepush($post_id, 'pokemon_evolution_image', $pokeEvolImageString);
            pokepush($post_id, 'pokemon_next', $pokeNextTitle); // next
            pokepush($post_id, 'pokemon_next_image', $pokeNextImage);
            pokepush($post_id, 'pokemon_next_class', $pokeNextClass);
            pokepush($post_id, 'pokemon_prev', $pokePrevTitle); // previous
            pokepush($post_id, 'pokemon_prev_image', $pokePrevImage);
            pokepush($post_id, 'pokemon_prev_class', $pokePrevClass);
            // notification
            echo $outMsg;
        }

        function pokedex_custom_js() {   
            wp_enqueue_script( 'pokedex_scripts', plugin_dir_url( __FILE__ ) . 'dist/js/primetime-pokedex.js', array('jquery'), '1.0' );
        }
    }
}

$var = new PrimetimePokedex();

add_action( 'plugins_loaded', array( $var, 'hooks' ) );