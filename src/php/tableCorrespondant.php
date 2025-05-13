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

function tableCorrespondantShow()
{
    // connect();
    $conn = oci_connect('pstest', 'ennov', 'TRA_ENNOV_01_R', 'utf8');

    if (!$conn) {
         $e = oci_error();
         trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
     }
    $req = "select * from CORRESPONDANTS"; //labos where ville = 'LOOS'

    $stid = oci_parse($conn, $req);
    oci_execute($stid);
    $nrows = oci_fetch_all($stid, $results);

    echo '<div class="row align-items-start">
      <table id="dataCorsp" class="table table-striped mb-0">
        <thead>
          <tr class="sticky">
            <th scope="col">Titre</th>
            <th scope="col">Nom</th>
            <th scope="col">Prénom</th>
            <th scope="col">Code Correspondant</th>
            <th scope="col">Code Labo</th>
          </tr>
        </thead>
        <tbody>';

    if ($nrows > 0 ) {

        for ($i=0; $i < $nrows; $i++){
          $codecorresp=$results["CODE_CORRESP"][$i]+1;

          echo '<tr id="swalCorresp" class="tr_tab" style="height: 52px;">
            <td id="titre" class="u-table-cell">'.$results["TITRE"][$i].'</td>
            <td class="u-table-cell">'.$results["NOM"][$i].'</td>
            <td class="u-table-cell">'.$results["PRENOM"][$i].'</td>
            <td class="u-table-cell">'.$results["CODE_CORRESP"][$i].'</td>
            <td class="u-table-cell">'.$results["CODE_LABO"][$i].'</td>
            <td class="u-table-cell"><a type="button" class="btn btn-outline-light space" href="details.php?id=', urlencode($results["CODE_CORRESP"][$i]),'"> Details </a></td>
          </tr>';
        }
    }
    echo ' </tbody>
      </table>
    </div>';

    oci_free_statement($stid);
    oci_close($conn);
}
