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

    if ($nrows > 0 ) {

        

    echo '<div class="row align-items-start">
      <table class="table table-striped mb-0">
        <thead>
          <tr class="sticky">
            <th scope="col">Code Correspondant</th>
            <th scope="col">Code Labo</th>
            <th scope="col">Nom</th>
            <th scope="col">Prénom</th>
          </tr>
        </thead>
        <tbody>
          <tr style="height: 52px;">
            <td class="u-table-cell">65</td>
            <td class="u-table-cell">84</td>
            <td class="u-table-cell">HESSNER</td>
            <td class="u-table-cell">Thomas</td>
          </tr>
        </tbody>
      </table>
    </div>';
    }

    oci_free_statement($stid);
    oci_close($conn);
}
