<!DOCTYPE html>
<html>
<head>

<style>
    body {
        font-family: Arial;
    }
    h1 {
      padding-top: 12px;
      padding-bottom: 12px;
      padding-left: 8px;
      margin: 0;
      margin-bottom: 5px;
      text-align: left;
      background-color: #424242;
      color: white;
    }
    table {
      margin-top:8px;
      border-collapse: collapse;
      width: 100%;
    }
    
    td, th {
      border: 1px solid #ddd;
      padding: 8px;
    }
    
    tr:nth-child(even){background-color: #f2f2f2;}
    
    tr:hover {background-color: #ddd;}
    
    th {
      padding-top: 12px;
      padding-bottom: 12px;
      text-align: left;
      background-color: #424242;
      color: white;
    }
    
    input[type=text], input[type=submit], button[type=submit], select{
      border:none;
      -webkit-border-radius: 5;
      border-radius: 5px;
      background: #ddd;
      padding: 5px 20px 5px 20px;
      margin-right: 5px;
      
    }
    input[type=text]:hover, input[type=submit]:hover, button[type=submit]:hover {
      background: #cccccc;
      text-decoration: none;
      cursor: pointer;
    }
    
    a:link {
      text-decoration: none;
    }
    
    a:visited {
      text-decoration: none;
    }


</style>
</head>
<body>
    <a href="http://hfyeg1.mercury.nottingham.edu.my/"><h1>SAKILA DATABASE</h1></a>


<?php
      
    // Create connection
    $conn = mysqli_connect(localhost, hfyeg1, sakiladatabase, hfyeg1_SAKILA);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: ". $conn->connect_error);
    }
    
    //get parameter from URL
    $selectedTable = $_GET['tables'];

    /*-------------------------------------------------------------------------------------------------------------------*/
    
    //get an array of all db table names
    $allTables = array_column(mysqli_fetch_all($conn->query('SHOW TABLES')),0);
    
    
    //dropdown menu of table names
    echo "\t".'<form action="" method="get" >';
    echo "\t".'<select name="tables" ">'."\n"; 
    echo "\t\t"."<OPTION value=0>Select a table</OPTION>"."\n"; 
    foreach($allTables as $tableName){
        echo "\t\t".'<OPTION value="'.$tableName.'"';
        if ($selectedTable == $tableName) //keep selected value after refresh
            echo ' selected';
        echo '>'.$tableName.'</OPTION>'."\n"; 
    }
    echo "\t".'</select>'; 
    
    //submit table 
    echo '<input type="submit" name="button" value="Submit"/></form>'."\n";
    
    /*-----------------------------------------------------------------------------------------------------------------------------*/
       
    //get selected table
    $selectedTable = $_GET["tables"];

    //get array of fields/columns in selected table
    $resultCol = $conn->query("SHOW COLUMNS FROM $selectedTable");
    while($row = $resultCol->fetch_assoc()){
        $columns[] = $row['Field'];
    }
    
    //find the primary key
    $r = $conn->query("SELECT key_column_usage.column_name FROM information_schema.key_column_usage WHERE table_schema = schema() AND constraint_name = 'PRIMARY' AND table_name = '$selectedTable'");
    if ($r->num_rows > 0) {
        // output data of each row
        while($row = $r->fetch_assoc()) {
            $primary= $row["column_name"];
        }
    } 
    else {
        echo "0 results";
    }      
    
    //hide the datetime column
     $d = $conn->query("DESCRIBE $selectedTable");
    if ($d->num_rows > 0) {
        // output data of each row
        while($row = $d->fetch_assoc()) {
            if($row['Type'] == 'datetime'){
                $datetime = $row['Field'];
            }
        }
    } 
    else {
        echo "0 results";
    }
    
    /*-----------------------------------------------------------------------------------------------------------------------------*/
   
    //checkboxes of table names
    echo "\n\t".'<form style="margin-top:4px;" action="" method="get" ">'."\n"; 
    echo '<input type="hidden" name="tables" value="'.$selectedTable.'">';
    foreach($columns as $selectedFields){
             echo "\t\t".'<label><input type="checkbox" name="fields[]" value="'.$selectedFields.'"';
            if($_GET["fields"]==NULL){ // check all values by default
                echo ' checked';
            }
            else foreach($_GET['fields'] as $checked){ //keep checked value after refresh
                if ($checked==$selectedFields)
                    echo ' checked';
            }
            echo '>'.$selectedFields.'</input></label>'."\n"; 
        
    }
    
    //SELECT AND INSERT BUTTON
    echo "\t\t".'<br><input type="submit" name="select" value="SELECT" style="margin-top:4px;"/>'."\n";
    echo "\t\t".'<input type="submit" name="insert" value="INSERT" /></form>'."\n";
    
   /*-------------------------------------------------------------------------------------------------------------------*/
   //INSERT FUNCTION------------------------------------------------------------------------------------------------------
    if(isset($_GET["insert"])){
        echo "<br>"."enter information"."<br>";
        echo '<form action="" method="get">';
        echo '<input type="hidden" name="tables" value="'.$selectedTable.'">';
        foreach($_GET['fields'] as $selected){
            echo '<input type="hidden" name="fields[]" value="'.$selected.'">';
        }
        
        //sql builder and generate forms
        $sql = 'INSERT INTO ' .$selectedTable.'(';
        foreach($_GET['fields'] as $selected){
            if($selected == $datetime){
          
            }
            else{
                echo '<input type="text" name="valueIns[]" placeholder="'.$selected.'" value="" required>'.'<br><br>';
                $sqlBuild .= ' '.$selected.',';
            }
        }
    
        $sql .= rtrim($sqlBuild,',').')'; //remove the last ','
        echo '<input type="submit" name="insert2" value="Submit">';
        echo '<input type="hidden" name="sql" value="'.$sql.'">';
        echo "</form>";
    }
    
    if(isset($_GET['insert2'])){
        //build sql command
        $sql= $_GET['sql'];
        $sql .= ' VALUES (';
        if(!empty($_GET['valueIns'])){
            foreach($_GET['valueIns'] as $selected){
                $sqlBuild .= '"'.$selected.'",';
            }
        }
        $sql .= rtrim($sqlBuild,',').')' ; //remove the last ','
    
        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
 
    /*-------------------------------------------------------------------------------------------------------------------*/
    
    //UPDATE FUNCTION
    if(isset($_GET['update'])){
        $value = $_GET['update'];

        echo "<br>"."Enter information"."<br>";
        echo '<form action="" method="get">';
        echo '<input type="hidden" name="tables" value="'.$selectedTable.'">';
        //generate forms
        
        foreach($_GET['fields'] as $selected){
            if($selected == $primary){
                echo '<input type="text" name="valueUpd[]" placeholder="" value="'.$value.'" >'.'<br><br>';
                echo '<input type="hidden" name="fieldsUpd[]" value="'.$selected.'">';
            }
            else if($selected == $datetime){
                echo '<input type="hidden" name="fieldsUpd[]" value="'.$selected.'">';
            }
            else{
                echo '<input type="text" name="valueUpd[]" placeholder="'.$selected.'" value="" >'.'<br><br>';
                echo '<input type="hidden" name="fieldsUpd[]" value="'.$selected.'">';
            }
            
        }
        
        $sql .= rtrim($sqlBuild,',').')'; //remove the last ','
        echo '<input type="hidden" name="tables" value="'.$selectedTable.'">';
        foreach($_GET['fields'] as $selected){
            echo '<input type="hidden" name="fields[]" value="'.$selected.'">';
        }
        echo '<input type="submit" name="update2" value="Submit">';

        echo "</form>";
    }
    
    if(isset($_GET['update2'])){
        //build sql command
        $sqlBuild = 'UPDATE ' .$selectedTable.' SET';
        for ($i = 1; $i < count($_GET['fieldsUpd']); $i++) {
            if($_GET['fieldsUpd'][$i] == $datetime){
                $sqlBuild .= ' '.$_GET['fieldsUpd'][$i].'= NOW(),'; 
            }
        
            else{
            $sqlBuild .= ' '.$_GET['fieldsUpd'][$i].'='.'"'.$_GET['valueUpd'][$i].'"'.',';
            }
        }
        $sql .= rtrim($sqlBuild,',').' WHERE '.$_GET['fieldsUpd'][0].'='.$_GET['valueUpd'][0];
        if ($conn->query($sql) === TRUE) {
            echo "Record updated successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    
    /*-------------------------------------------------------------------------------------------------------------------*/
    
    //DELETE FUNCTION------------------------------------------------------------------------------------------------------
    if(isset($_GET['delete'])){
        $value = $_GET['delete'];
        $sql = "DELETE FROM $selectedTable WHERE $primary = $value ";
        
        echo '<br>';
        if ($conn->query($sql) === TRUE) {
            echo "Record delete successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    
    
    /*-----------------------------------------------------------------------------------------------------------------------------*/
    //SELECT FUNCTION---------------------------------------------------------------------------------------------------------------
    if(isset($_GET['select']) || isset($_GET['update']) || isset($_GET['delete']) || isset($_GET['update2']) || isset($_GET['insert']) || isset($_GET['insert2'])){
        
        echo '<table><tr>';
        
        //build sql command
        $sqlBuild = 'SELECT';
        if(!empty($_GET['fields'])){
            foreach($_GET['fields'] as $selected){
                echo '<th>'.$selected.'</th>';
                $sqlBuild .= ' '.$selected.',';
            }
            echo '<th>ACTIONS</th>';
            $sql = rtrim($sqlBuild,',').' FROM '.$selectedTable; //remove the last ','
        }
        echo '</tr>';
    
        $result = $conn->query($sql);
        
        //generate table
        echo '<form action="" method="get">';
        echo '<input type="hidden" name="tables" value="'.$selectedTable.'">';
        foreach($_GET['fields'] as $selected){
            echo '<input type="hidden" name="fields[]" value="'.$selected.'">';
        }
        
        if ($result-> num_rows > 0) {
            while($row = $result-> fetch_assoc()) {
                echo '<tr>';
                foreach($_GET['fields'] as $selected){
                    if($selected == $primary){
                        $pri = $row[$primary];
                    }
                    if($row[$selected]== NULL){
                        $row[$selected] = 'NULL';
                    }
                    echo '<td>'.$row[$selected].'</td>';
         
                }
                //update and delete button
                echo '<td> 
                
                <button type="submit" name="update" value="'.$pri.'" style="margin-bottom:3px">UPDATE</button>
                <button type="submit" name="delete" value="'.$pri.'">DELETE</button></td>';
                echo '</tr>'; 
            }
            echo "</table>";
        } else {
            echo "0 results";
        }
       echo '</form>';
    }
    
    
    
    
    $conn-> close();
?> 
</table>

</body>
</html>
