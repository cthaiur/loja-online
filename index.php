<?php
$config = parse_ini_file('config/config.ini');
define('ROOT', getcwd());
define('THEME', $config['site_template']);
define('THEME_PATH', ROOT.'/templates/'.THEME);

// load framework classes
chdir('admin');
require_once 'admin/init.php';
require_once 'lib/route/Route.php';

// default action
Route::on('', function($args)
{
    TTransaction::open('blog');
    $category = Category::first();
    Route::exec('show_category', ['category_id'=> $category->id] );
    TTransaction::close();
});

// render a category
Route::on('show_category', function($args)
{
    $limit = 9;
    $offset = empty($args['offset']) ? 0 : $args['offset'];
    
    TTransaction::open('blog');
    $category    = !empty($args['category_id']) ? Category::find($args['category_id']) : Category::first();
    
    if (empty($category))
    {
        throw new Exception('Categoria não encontrada');
    }
    
    $posts       = $category->getPosts( $offset, $limit );
    $post_tpl    = file_exists(THEME_PATH.'/partials/post-'.$category->id.'.html') ? 'post-'.$category->id : 'post';
    $content     = PostService::renderMany($posts, file_get_contents(THEME_PATH.'/partials/'.$post_tpl.'.html'));
    $new_offset  = $offset + $limit;
    
    // se tem mais registros, mostra o botao mais...
    if (Post::where('category_id', '=', $category->id)->count() > $new_offset)
    {
        $more_tpl = file_get_contents(THEME_PATH.'/partials/more.html');
        $more_tpl = str_replace('{action}', "index.php?action=show_category&category_id={$category->id}&offset={$new_offset}", $more_tpl);
        $content .= $more_tpl;
    }
    TTransaction::close();
    
    // render home (post collection)
    if (count($posts) > 1 or isset($args['offset']))
    {
        $replaces = [];
        $replaces['posts']                = $content;
        $replaces['category_id']          = $category->id;
        $replaces['category_name']        = $category->name;
        $replaces['category_description'] = $category->description;
        $replaces['category-image']       = $category->image;
        
        if (file_exists(THEME_PATH.'/home-'.$category->id.'.html'))
        {
            render_content( $replaces, 'home-'.$category->id );
        }
        else
        {
            render_content( $replaces, 'home' );
        }
    }
    else if (count($posts) == 1) // render a specific post
    {
        Route::exec('show_post', ['post_id'=> $posts[0]->id] );
    }
});

// render a post
Route::on('show_post', function($args)
{
    if (!empty($args['post_id']))
    {
        TTransaction::open('blog');
        $post = Post::find( (int) $args['post_id'] );
        if ($post)
        {
            $replaces              = $post->toArray();
            $replaces['author']    = $post->user_name;
            $replaces['media']     = PostService::renderMedia($post);
            $replaces['past_time'] = TimeTools::getShortPastTime($post->post_date);
            TTransaction::close();
            
            $post_tpl = file_exists(THEME_PATH.'/post-'.$post->category_id.'.html') ? 'post-'.$post->category_id : 'post';
            render_content( $replaces, $post_tpl );
        }
    }
});

// render a year
Route::on('show_year', function($args)
{
    TTransaction::open('blog');
    $post_tpl = file_get_contents(THEME_PATH.'/partials/post.html');
    $posts    = Post::getForYear( $args['year'] );
    $content  = PostService::renderMany($posts, $post_tpl);
    TTransaction::close();
    
    render_content( ['posts' => $content, 'category_name' => 'Resultados', 'category_description' => ''], 'home' );
});

// render a year
Route::on('show_tag', function($args)
{
    TTransaction::open('blog');
    $post_tpl = file_get_contents(THEME_PATH.'/partials/post.html');
    $posts    = Post::getForTag( $args['tag'] );
    $content  = PostService::renderMany($posts, $post_tpl);
    TTransaction::close();
    
    render_content( ['posts' => $content, 'category_name' => 'Resultados', 'category_description' => ''], 'home' );
});

// search a post
Route::on('search_post', function($args)
{
    if (!empty($args['search']))
    {
        TTransaction::open('blog');
        $search   = $args['search'];
        $post_tpl = file_get_contents(THEME_PATH.'/partials/post.html');
        $posts    = Post::where('title', 'like', "%{$search}%")->orWhere('subtitle', 'like', "%{$search}%")->orWhere('content', 'like', "%{$search}%")->load();
        $content  = PostService::renderMany($posts, $post_tpl);
        TTransaction::close();
        
        render_content( ['posts' => $content, 'category_name' => 'Resultados', 'category_description' => ''], 'home' );
    }
});

// search a post
Route::on('send_contact', function($args)
{
    TTransaction::open('permission');
    $preferences = SystemPreference::getAllPreferences();
    TTransaction::close();
    
    if (!empty($args['message']))
    {
        MailService::send(trim($preferences['mail_support']), 'Contact', $args['message'], 'text');
    }
    
    render_content( ['posts' => 'Email enviado com sucesso', 'category_name' => 'Resultado', 'category_description' => ''], 'home' );
});

Route::on('render_categories', function($args)
{
    $config         = parse_ini_file('config/config.ini');
    $category_id    = $replaces['category_id'] ?? null;
    
    TTransaction::open('blog');
    $category_tpl   = file_get_contents(THEME_PATH.'/partials/category.html');
    $categories     = CategoryService::render($category_tpl, $category_id);
    
    TTransaction::close();
    
    print $categories;
});


Route::on('vai_teste', function($args)
{
    render_content( ['title' => 'VAI TESTE', 'subtitle' => 'Esse teste funcionou', 'content' => 'aqui é meu post'], 'post');
});


// in case of exception
Route::exception( function($exception)
{
    render_content( ['content' => $exception->getMessage(), 'title' => 'Error'], 'error' );
});


Route::run();

// render page content
function render_content($replaces, $layout = 'home')
{
    $config         = parse_ini_file('config/config.ini');
    $layout_content = file_get_contents(THEME_PATH.'/'.$layout.'.html');
    $category_id    = $replaces['category_id'] ?? null;
    
    TTransaction::open('blog');
    $category_tpl   = file_get_contents(THEME_PATH.'/partials/category.html');
    $archives_tpl   = file_get_contents(THEME_PATH.'/partials/archive.html');
    $tags_tpl       = file_get_contents(THEME_PATH.'/partials/tag.html');
    
    $categories     = CategoryService::render($category_tpl, $category_id);
    $archives       = PostService::renderYears($archives_tpl);
    $tags           = PostService::renderTags($tags_tpl, $category_id);
    $blocks         = Block::getIndexedArray('name', 'content');
    
    $replaces       = array_merge($replaces, $blocks);
    
    foreach ($replaces as $key => $value)
    {
        $layout_content = str_replace('{'.$key.'}', (string) $value, $layout_content);
    }
    
    $layout_content = str_replace('{theme}', THEME, $layout_content);
    $layout_content = str_replace('{url}', $_SERVER['SCRIPT_NAME'], $layout_content);
    $layout_content = str_replace('{site_title}', $config['site_title'], $layout_content);
    $layout_content = str_replace('{site_subtitle}', $config['site_subtitle'], $layout_content);
    $layout_content = str_replace('{archives}', $archives, $layout_content);
    $layout_content = str_replace('{categories}', $categories, $layout_content);
    $layout_content = str_replace('{taglist}', $tags, $layout_content);
    
    TTransaction::close();
    
    echo $layout_content;
}
