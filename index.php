<?php

// Postgresql Info
$connection_url = parse_url(getenv('CLEARDB_DATABASE_URL'));
$db['host'] = $connection_url['host'];
$db['name'] = explode('/', $connection_url['path'])[1];
$db['username'] = $connection_url['user'];
$db['password'] = $connection_url['pass'];

// Set up autoloader
$loader = new \Phalcon\Loader();

// Load models
$loader->registerDirs([__DIR__ . '/models/'])->register();

// Dependency injector
$di = new \Phalcon\DI\FactoryDefault();

// Set up database connection
$di->set('db', function () use ($db) {
	return new \Phalcon\Db\Adapter\Pdo\Mysql([
		'host'     => $db['host'],
		'dbname'   => $db['name'],
		'username' => $db['username'],
		'password' => $db['password']
	]);
});

// Create new micro application
$app = new \Phalcon\Mvc\Micro();
$app->setDI($di);

//Setting views directory
$view = new Phalcon\Mvc\View();
$view->setViewsDir(__DIR__ . '/views/');

$setupView = function ($path = [], $vars = []) use ($view) {
  $view->start();
  foreach ($vars as $key => $var) {
    $view->setVar($key, $var);
  }
  $view->render($path[0], $path[1]);
  $view->finish();
  return $view->getContent();
};

// Posts index
$app->get('/', function () use ($setupView) {
  $posts = Posts::find(['order' => 'id DESC']);
  echo $setupView(['posts', 'index'], ['posts' => $posts]);
});

// Show post
$app->get('/posts/{id}', function ($id) use ($setupView) {
	$post = Posts::findFirst((int) $id);
  echo $setupView(['posts', 'show'], ['post' => $post]);
});

// New post
$app->get('/posts/new', function () use ($setupView) {
  echo $setupView(['posts', 'new']);
});

// Create post
$app->post('/posts/', function () use ($app) {
  $post = new Posts();
  $post->title = $app->request->getPost('title');
  $post->body = $app->request->getPost('body');
  $post->save();
  header('location: /');
});

// Create comment
$app->post('/comments/', function () use ($app) {
  $post_id = $app->request->getPost('post_id');

  $comment = new Comments();
  $comment->post_id = (int) $post_id;
  $comment->name = $app->request->getPost('name');
  $comment->comment = $app->request->getPost('comment');
  $comment->save();

  header('location: /posts/' . $post_id);
});

$app->notFound(function () use ($app) {
  $app->response->setStatusCode(404, "Not Found")->sendHeaders();
  echo 'Page not found.';
});

$app->handle();