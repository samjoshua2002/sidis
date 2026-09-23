<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';


$error = '';

$cluster_name = '';


/*
|--------------------------------------------------------------------------
| PROCESS ADD FORM
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{

    $cluster_name =
        trim($_POST['cluster_name'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($cluster_name === '')
    {

        $error =
            'Please enter the cluster name.';

    }


    /*
    |--------------------------------------------------------------------------
    | CHECK DUPLICATE
    |--------------------------------------------------------------------------
    */

    if ($error === '')
    {

        $stmt = $pdo->prepare("
            SELECT
                id
            FROM clusters
            WHERE cluster_name = ?
            LIMIT 1
        ");


        $stmt->execute([
            $cluster_name
        ]);


        if ($stmt->fetch())
        {

            $error =
                'This cluster already exists.';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

    if ($error === '')
    {

        try
        {

            $stmt = $pdo->prepare("
                INSERT INTO clusters
                (
                    cluster_name,
                    status
                )
                VALUES
                (
                    ?,
                    1
                )
            ");


            $stmt->execute([
                $cluster_name
            ]);


            /*
            |--------------------------------------------------------------------------
            | REDIRECT BEFORE HEADER.PHP
            |--------------------------------------------------------------------------
            */

            header(
                'Location: clusters.php?success=added'
            );

            exit;

        }
        catch (PDOException $e)
        {

            $error =
                'Unable to add the cluster. Please try again.';

        }

    }

}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

include 'header.php';

?>


<div class="dashboard-content">


    <!-- PAGE HEADER -->

    <div class="page-header">

        <div>

            <h2>Add Cluster</h2>

            <p>
                Add a new cluster to the website.
            </p>

        </div>

    </div>


    <!-- ERROR -->

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars(
                $error,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </div>

    <?php endif; ?>


    <!-- FORM CARD -->

    <div class="dashboard-card">


        <div class="card-header">

            <div>

                <h3>Cluster Details</h3>

                <p>
                    Enter the cluster details.
                </p>

            </div>

        </div>


        <form
            method="POST"
            action=""
        >


            <!-- CLUSTER NAME -->

            <div class="form-group">

                <label for="cluster_name">
                    Cluster Name
                </label>


                <input
                    type="text"
                    id="cluster_name"
                    name="cluster_name"
                    class="form-control"
                    value="<?= htmlspecialchars(
                        $cluster_name,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    placeholder="Enter cluster name"
                    required
                >

            </div>


            <!-- BUTTONS -->

            <div class="form-actions">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Add Cluster
                </button>


                <a
                    href="clusters.php"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>

            </div>


        </form>

    </div>

</div>


<?php include 'footer.php'; ?>