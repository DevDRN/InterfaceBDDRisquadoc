<?php
require 'connexion.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $function = $_POST["call"];

    if (function_exists($function)) {
        call_user_func($function);
    } else {
        echo 'Function Not Exists!!';
    }
}

function tableLaboShow()
{
    // connect();
    $conn = oci_connect('pstest', 'ennov', 'TRA_ENNOV_01_R', 'utf8');

    if (!$conn) {
         $e = oci_error();
         trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
     }
    $req = "select * from LABOS"; //labos where ville = 'LOOS'

    $stid = oci_parse($conn, $req);
    oci_execute($stid);
    $nrows = oci_fetch_all($stid, $results);

    echo '<div class="row align-items-start">
      <table class="table table-striped mb-0">
        <thead>
          <tr class="sticky">
            <th scope="col">Nom du Labo</th>
            <th scope="col">Ville</th>
            <th scope="col">Code Postal</th>
            <th scope="col">Pays</th>
          </tr>
        </thead>
        <tbody>';
            //<th scope="col">Code Labo</th>

    if ($nrows > 0 ) {

        for ($i=0; $i < $nrows; $i++){

          echo '<tr style="height: 52px;">
            <td class="u-table-cell">'.$results["NOM_LABO"][$i].'</td>
            <td class="u-table-cell">'.$results["VILLE"][$i].'</td>
            <td class="u-table-cell">'.$results["CP"][$i].'</td>
            <td class="u-table-cell">'.$results["PAYS"][$i].'</td>
            <td class="u-table-cell"><a type="button" class="btn btn-outline-light space" href="detailsL.php?id=', urlencode($results["CODE_LABO"][$i]),'"> Details </a></td>
          </tr>';
            //<td class="u-table-cell">'.$results["CODE_LABO"][$i].'</td>
        }
    }
    echo ' </tbody>
      </table>
    </div>';

    oci_free_statement($stid);
    oci_close($conn);
}

function searchLab($nomLabo) {
  require "connexion.php";
  $sql= "SELECT * FROM LABOS WHERE NOM_LABO = '".$nomLabo."'";
  $stid = oci_parse($conn, $sql);
  oci_execute($stid);
  $nrow = oci_fetch_assoc($stid, $result);

  echo '<div class="row align-items-start">
      <table class="table table-striped mb-0">
        <thead>
          <tr class="sticky">
            <th scope="col">Nom du Labo</th>
            <th scope="col">Ville</th>
            <th scope="col">Code Postal</th>
            <th scope="col">Pays</th>
          </tr>
        </thead>
        <tbody>';
  
      if ($nrows > 0 ) {

        for ($i=0; $i < $nrows; $i++){

          echo '<tr style="height: 52px;">
            <td class="u-table-cell">'.$results["NOM_LABO"][$i].'</td>
            <td class="u-table-cell">'.$results["VILLE"][$i].'</td>
            <td class="u-table-cell">'.$results["CP"][$i].'</td>
            <td class="u-table-cell">'.$results["PAYS"][$i].'</td>
            <td class="u-table-cell"><a type="button" class="btn btn-outline-light space" href="detailsL.php?id=', urlencode($results["CODE_LABO"][$i]),'"> Details </a></td>
          </tr>';
            //<td class="u-table-cell">'.$results["CODE_LABO"][$i].'</td>
        }
    }
    echo ' </tbody>
      </table>
    </div>';

    oci_free_statement($stid);
    oci_close($conn);

}