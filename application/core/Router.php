<?php


class Router
{
    protected $routers;

    public function __construct($definitions){
        $this->routers = $this->compileRouters($definitions);
    }

    public function compileRouters($definitions){
        $routers = array();

        foreach ($definitions as $url => $params){
            $tokens = explode('/', ltrim($url, '/'));
            foreach ($tokens as $i => $token){
                if (0 === strpos($token, ':')){
                    $name = substr($token, 1);
                    $token = '(?P<' . $name . '>[^/]+)';
                }
                $tokens[$i] = $token;
            }

            $pattern = '/'. implode('/', $tokens);
            $routers[$pattern] = $params;
        }
        return $routers;
    }

    public function resolve($path_info){
        if ('/' !== substr($path_info, 0, 1)){
            $path_info = '/'. $path_info;
        }
        foreach ($this->routers as $pattern => $params){
            if (preg_match('#^'. $pattern . '$#', $path_info, $matches)){
                $params = array_merge($params, $matches);

                return $params;
            }
        }
        return false;
    }
}