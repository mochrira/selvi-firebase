# Selvi Firebase

Fastest way to integrate selvi-framework with firebase

## Setup

Execute following lines on your cli

```
$ composer require mochrira/selvi-framework
$ composer require mochrira/selvi-firebase
```

## Configuration (Single Database)

Setup this package by adding following lines before `Selvi\Framework::run()` on your `index.php`

```
\Selvi\Firebase\Manager::setup(['serviceAccountFile' => 'path to your service account json']);
\Selvi\Firebase\Pengguna::setup(['schema' => 'your main schema']);
```

## Setup Database

Setup your database by running following command on your project directory

```
$ php index.php migrate main up
```

Then, check your database, and you will see the default database structure for firebase project

## Accept Authorization header

To accept authorization header, add following lines to the end of your `.htaccess`

```
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
```
Then add following to the top of your `index.php`

```
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, authorization");
```

## Creating Custom Controller

To validate every request to your controller, you must create custom controller for your application. Create `Controller.php` inside your project's `app` folder, and fill with the following script:

```
<?php 

namespace App;
use Selvi\Controller as SelviController;
use Selvi\Firebase\Pengguna;

class Controller extends SelviController{

    function validateToken() {
        Pengguna::validateToken();
    }

    function validatePengguna() {
        Pengguna::validatePengguna();
    }

}
```

Last step, extends above class to all of your app controllers e.g. KontakController, then call `validateToken` and `validatePengguna` like below.

```
<?php 

namespace App\Controllers;
use App\Controller; /** import your custom controller */
use Selvi\Exception;
use App\Models\Kontak;

class KontakController extends Controller { /** extends that*/

    function __construct() {
        $this->validateToken(); /** To validate token **/
        $this->validatePengguna(); /** To validate pengguna **/
        $this->load(Kontak::class, 'Kontak');
    }

    function rowException($idKontak) {
        $data = $this->Kontak->row($idKontak);
        if(!$data) {
            Throw new Exception('Kontak not found', 'kontak/not-found', 404);
        }
        return $data;
    }

    function result() {
        $order = [];
        $sort = $this->input->get('sort');
        if($sort !== null) {
            $order = \buildOrder($sort);
        }

        $orWhere = [];
        $search = $this->input->get('search');
        if($search !== null) {
            $orWhere = \buildSearch(['nmKontak'], $search);
        }

        $where = [];
        return jsonResponse($this->Kontak->result($where, $orWhere, $order));
    }

    function row() {
        $idKontak = $this->uri->segment(2);
        $data = $this->rowException($idKontak);
        return jsonResponse($data);
    }

    function insert() {
        $data = json_decode($this->input->raw(), true);
        $idKontak = $this->Kontak->insert($data);
        if($idKontak === false) {
            Throw new Exception('Failed to insert', 'kontak/insert-failed');
        }
        return jsonResponse(['idKontak' => $idKontak], 201);
    }

    function update() {
        $idKontak = $this->uri->segment(2);
        $this->rowException($idKontak);
        $data = json_decode($this->input->raw(), true);
        if(!$this->Kontak->update($idKontak, $data)) {
            Throw new Exception('Failed to insert', 'kontak/insert-failed');
        }
        return response('', 204);
    }

    function delete() {
        $idKontak = $this->uri->segment(2);
        $this->rowException($idKontak);
        if(!$this->Kontak->delete($idKontak)) {
            Throw new Exception('Failed to insert', 'kontak/insert-failed');
        }
        return response('', 204);
    }

}
```