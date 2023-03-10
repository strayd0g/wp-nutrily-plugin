<?php
include 'support.php';


function get_foodname_data($get_food_name) {

    global $wpdb;
    $foodname_table = "vegan_foodname_de";

    $query = "SELECT * FROM $foodname_table WHERE foodname LIKE '%$get_food_name%'";
    $data = $wpdb->get_results($query);

    return $data;
}

function get_pagination($prev, $next) {

    global $wpdb;

    $query = "SELECT meal_id, meal_name, tags, meal_temperature, timestamp FROM `vegan_users_meal` ORDER BY `timestamp` DESC LIMIT $prev, $next";
    $data = $wpdb->get_results($query);

    return $data;
}

function create_user_meal(

  $meal_name,
  $ingredients_names,
  $prep_steps,
  $prep_time,
  $total_time,
  $tags,
  $meal_temperature,
  $meal_type,
  $ingredients_type,
  $img_link,
  $general_note,
  $optional_note,
  $optional_ingredients
  
  ) {

    global $wpdb;
    $date = date('Y-m-d H:i:s');
    $key = uniqid();

    $res1 = $wpdb->insert('vegan_users_meal', 
        array(
            'meal_id' => $key,
            'meal_name' => $meal_name,
            'prep_time' => $prep_time,
            'prep_total_time' => $total_time,
            'tags' => $tags,
            'meal_temperature' => $meal_temperature,
            'meal_type' => $meal_type,
            'ingredients_types' => $ingredients_type,
            'timestamp' => $date,
            )
    );

    $optional_ingredients_data = array();
    $ingredient_names_data = array();
    $optional_ingredients_data_2 = array();

    $optional_ingredients_arr = json_decode($optional_ingredients, true);

    if(count($optional_ingredients_arr) != 1) {
    for ($i = 0; $i < count($optional_ingredients_arr); $i++)
    {
        array_push(
          $optional_ingredients_data,
          array('odid' => '', 'foodname' => $optional_ingredients_arr[$i]["foodname"]),
        );
        array_push(
          $optional_ingredients_data_2,
          array( 'meal_id' => $key, 'foodname' => $optional_ingredients_arr[$i]["foodname"], 'qty' => $optional_ingredients_arr[$i]["quantity"], 'unit' => $optional_ingredients_arr[$i]["unit"]),
        );
    }
    
    insert_multiple_rows('vegan_foodname_de', $optional_ingredients_data);
    insert_multiple_rows('vegan_ingredient_data_optional', $optional_ingredients_data_2);
  } else {
    $wpdb->insert('vegan_foodname_de', array('odid' => '', 'foodname' => $optional_ingredients_arr[0]["foodname"]));
    $wpdb->insert('vegan_ingredient_data_optional', array( 'meal_id' => $key, 'foodname' => $optional_ingredients_arr[0]["foodname"], 'qty' => $optional_ingredients_arr[0]["quantity"], 'unit' => $optional_ingredients_arr[0]["unit"]));
  }


    $prep_step_data = array();
    $prep_steps_arr = json_decode($prep_steps, true);

    if(count($prep_steps_arr) != 1) {
    for($i = 0; $i < count($prep_steps_arr); $i++) 
    {
        array_push(
            $prep_step_data,
            array('meal_id' => $key, 'steps' => $prep_steps_arr[$i]["steps"]),
        ); 
    }
    insert_multiple_rows('vegan_prep_steps', $prep_step_data);
   } else {
    $wpdb->insert('vegan_prep_steps', array('meal_id' => $key, 'steps' => $prep_steps_arr[0]["steps"]));
   }

    $ingredient_names_arr = json_decode($ingredients_names, true);
    if(count($ingredient_names_arr) != 1) {
    for ($i = 0; $i < count($ingredient_names_arr); $i++)
    {
        array_push(
            $ingredient_names_data,
            array( 'odid' => $ingredient_names_arr[$i]["odid"], 'meal_id' => $key, 'qty' => $ingredient_names_arr[$i]["qty"], 'unit' => $ingredient_names_arr[$i]["unit"]),
        );
    }
    
    insert_multiple_rows('vegan_ingredient_data', $ingredient_names_data);
    } else {
      $wpdb->insert('vegan_ingredient_data', array( 'odid' => $ingredient_names_arr[0]["odid"], 'meal_id' => $key, 'qty' => $ingredient_names_arr[0]["qty"], 'unit' => $ingredient_names_arr[0]["unit"]));
    }


    $img_link_arr = json_decode($img_link, true);
    if(count($img_link_arr) != 1) {
    $img_link_data = array();
    for ($i = 0; $i < count($img_link_arr); $i++)
    {
        array_push(
            $img_link_data,
            array('img_link' => $img_link_arr[$i]["img_link"], 'meal_id' => $key),
        );
    }
      insert_multiple_rows('vegan_meal_images', $img_link_data);
    } else {
      $wpdb->insert('vegan_meal_images', array('img_link' => $img_link_arr[0]["img_link"], 'meal_id' => $key));
    }

    // this is for meal notes
    $wpdb->insert('vegan_meal_notes', 
    array(
      'meal_id' => $key,
      'general_notes' => $general_note,
      'optional_notes' => $optional_note,
      ));
      

    if($res1) {
        return 'success';
    }
}

function delete_specific_meal($id) {
    global $wpdb;

    $wpdb->query("DELETE from vegan_prep_steps where meal_id= '$id'");
    $wpdb->query("DELETE from vegan_meal_images where meal_id= '$id'");
    $wpdb->query("DELETE from vegan_ingredient_data where meal_id= '$id'");
    $wpdb->query("DELETE from vegan_users_meal where meal_id= '$id'");
    
}


function get_meal($id_or_name) {
  global $wpdb;
  $data = $wpdb->get_results("SELECT * FROM `vegan_users_meal` WHERE `meal_name` LIKE '%$id_or_name%' OR `meal_id` = '$id_or_name'");
  return $data;
}


function get_nutritional_value($meal_id) {
  global $wpdb;
  $data = $wpdb->get_results("SELECT i_data.meal_id, i_data.odid, i_data.qty, c_value.EUFDNAME, cast(replace(c_value.BESTLOC, ',', '.') as decimal(18,2)) as BESTLOC FROM vegan_ingredient_data AS i_data 
  INNER JOIN vegan_component_value as c_value ON i_data.odid=c_value.ODID
  WHERE c_value.BESTLOC > '0' AND i_data.meal_id = '$meal_id'");
  return $data;
}

function get_details($id) {
    global $wpdb;

    $data = $wpdb->get_results("SELECT
    f.meal_id,
    f.meal_name,
    f.prep_time,
    f.prep_total_time,
    f.tags,
    f.meal_temperature,
    f.meal_type,
    f.ingredients_types,
    f.timestamp,
    (
      SELECT
        GROUP_CONCAT(ft.steps SEPARATOR ';')
      FROM
        vegan_prep_steps ft
      WHERE
        f.meal_id = ft.meal_id
    ) AS steps,
    (
      SELECT
        GROUP_CONCAT(ft.img_link SEPARATOR ';')
      FROM
        vegan_meal_images ft
      WHERE
        f.meal_id = ft.meal_id
    ) AS image_links,
    (
      SELECT
        GROUP_CONCAT(ft.foodname SEPARATOR ';')
      FROM
        vegan_foodname_de ft
        INNER JOIN vegan_ingredient_data i
      WHERE
        f.meal_id = i.meal_id
        AND ft.odid = i.odid
    ) AS ingredients_list,
     (
      SELECT
        GROUP_CONCAT(i.qty SEPARATOR ';')
      FROM
        vegan_foodname_de ft
        INNER JOIN vegan_ingredient_data i
      WHERE
        f.meal_id = i.meal_id
        AND ft.odid = i.odid
    ) AS quantity,
    (
      SELECT
        GROUP_CONCAT(ft.general_notes)
      FROM
      vegan_meal_notes ft
      WHERE
        f.meal_id = ft.meal_id
    ) AS general_notes,
    (
      SELECT
        GROUP_CONCAT(ft.optional_notes)
      FROM
      vegan_meal_notes ft
      WHERE
        f.meal_id = ft.meal_id
    ) AS optional_notes,
    (
      SELECT
        GROUP_CONCAT(ft.foodname SEPARATOR ';')
      FROM
        vegan_ingredient_data_optional ft
      WHERE
        f.meal_id = ft.meal_id
    ) AS optional_ingredients_list,
    (
      SELECT
        GROUP_CONCAT(ft.qty SEPARATOR ';')
      FROM
        vegan_ingredient_data_optional ft
      WHERE
        f.meal_id = ft.meal_id
    ) AS optional_ingredients_quantity
  FROM
    vegan_users_meal f WHERE f.meal_id = '$id'
  ");
    return $data;
}

?>

