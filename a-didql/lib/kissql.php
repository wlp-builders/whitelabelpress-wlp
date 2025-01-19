<?php
/*
Library Name: KissQL
Description: Keep It Super Simple Query Language (one auth function + securely expose functions)
Version: 1.0
Author: Neil
License: Spirit of Time 1.0
Year: 2024
*/
    class KissQl {
        private $routes = [];
        private $authFunction;
        private $docs = []; // Store descriptions for functions
        private $argDocs = []; // Store descriptions for function arguments
    
        // Set the authentication function
        public function setAuthFunction(callable $authFunction) {
            $this->authFunction = $authFunction;
        }

        public function getRoutes() {
            return $this->routes;
        }
    
        public function getRoute($ame) {
            return $this->routes[$name];
        }
    
        // Set description for a function using a key-value pair
        public function setDocs($key, $description) {
            $this->docs[$key] = $description;
        }
    
        // Set description for a specific argument of a function
        public function setArgumentDocs($functionKey, $paramName, $description) {
            $this->argDocs[$functionKey][$paramName] = $description;
        }
    
        // Generate route data in JSON format
        public function generateRouteJson($routeName) {
            if (!isset($this->routes[$routeName])) {
                throw new InvalidArgumentException("Route '$routeName' not found.");
            }
    
            $routeFunctions = $this->routes[$routeName];
            $jsonData = [
                'route' => $routeName,
                'functions' => []
            ];
    
            foreach ($routeFunctions as $functionName => $function) {
                $reflection = new ReflectionFunction($function);
                $params = [];
    
                // Iterate over function parameters to extract their names and types (if available)
                foreach ($reflection->getParameters() as $param) {
                    $paramName = $param->getName();
                    $paramType = (string) $param->getType();  // Type hinting if available
    
                    // Add argument description if set, otherwise fallback to 'No description available'
                    $paramDescription = $this->argDocs[$functionName][$paramName] ?? 'No description available';
    
                    $params[] = [
                        'name' => $paramName,
                        'type' => $paramType,
                        'description' => $paramDescription
                    ];
                }
    
                $jsonData['functions'][$functionName] = [
                    'arguments' => $params,
                    'description' => $this->docs[$functionName] ?? 'No description available'
                ];
            }
    
            return json_encode($jsonData, JSON_PRETTY_PRINT);
        }
    
        // Add a route with functions
        public function addRoute($routeName, array $functions) {
            log_message('Trying to add route for: '.($routeName));
    
            // Check if functions is an associative array
            if(!(array_keys($functions) !== range(0, count($functions) - 1))) {
                throw new InvalidArgumentException("Functions must be a non-empty associative array.");
            }
    
            // Ensure the route exists
            if (!isset($this->routes[$routeName])) {
                $this->routes[$routeName] = [];
            }
    
            log_message('Functions total: '.count($functions));
            foreach ($functions as $function => $value) {
                log_message('Function: '.$function);
    
                // Ensure function names follow the namespace convention (e.g., 'inbox__send')
                if (strpos($function, '__') === false) {
                    throw new InvalidArgumentException("Function name '$function' must follow the 'namespace__function' format.");
                }
    
                // Store the function and link to its description
                $this->routes[$routeName][$function] = $value;
            }
        }
    
        // Execute a function based on the route and function name
        public function execute($funcName, $args) {
            if (!$this->authFunction) {
                http_response_code(500);
                echo json_encode(['error' => 'authFunction is not set']);
                exit;
            }
    
            $authData = call_user_func($this->authFunction);
            $route = $authData['route'] ?? 'guest';
    
            // If no function is given, return the route documentation in JSON format
            if (!$funcName) {
                http_response_code(200);
                echo json_encode($this->generateRouteJson($route));
                die();
            }
    
            $decodedUser = $authData['decodedUser'] ?? [];
    
            if (!isset($this->routes[$route])) {
                http_response_code(404);
                echo json_encode(['error' => "Route '$route' not found"]);
                exit;
            }
    
            $routeFunctions = $this->routes[$route];
            if (!isset($routeFunctions[$funcName]) || !is_callable($routeFunctions[$funcName])) {
                http_response_code(404);
                echo json_encode(['error' => "Function '$funcName' not found in route '$route'"]);
                exit;
            }
    
            log_message('DecodedUser: '.json_encode($decodedUser));
            log_message('FunctionName: '.json_encode($funcName));
            log_message('Args: '.json_encode($args));
            
            // Merge the user data and function arguments
            $argsWithUser = array_merge([$decodedUser], $args);
            $result = call_user_func_array($routeFunctions[$funcName], $argsWithUser);
    
            http_response_code(200);
            echo json_encode($result[0]);
            die();
        }
    }
    
