<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usuario extends CI_Controller {
    /*
    Validação dos tipos de retornos nas validações (Código de erro)
    1 - Operação realizada no banco de dados com sucesso (Inserção, Aletração, Consulta e exclusão)
    2 - Conteúdo passado nulo ou vazio
    3 - Conteúdo zerado
    4 - Conteúdo não é inteiro
    5 - Conteúdo não é um texto
    8 - E-mail em formato inválido
    12 - Na atualização, pelo menos um atributo deve ser passado
    99 - Parâmetros pessados do frint não correspondem ao método

    */

    //Atributos privados da classe
    private $idUsuario;
    private $nome;
    private $email;
    private $usuario;
    private $senha;

    //Getters dos atributos
    public function getIdUsuario() {
        return $this->idUsuario;
    }
    public function getNome() {
        return $this->nome;
    }
    public function getEmail() {
        return $this->email;
    }
    public function getUsuario() {
        return $this->usuario;
    }
    public function getSenha() {
        return $this->senha;
    }

    //Setters dos atributos
    public function setIdUsuario($idUsuarioFront) {
        $this->idUsuario = $idUsuarioFront;
    }
    public function setNome($nomeFront) {
        $this->nome = $nomeFront;
    }
    public function setEmail($emailFront) {
        $this->email = $emailFront;
    }
    public function setUsuario($usuarioFront) {
        $this->usuario = $usuarioFront;
    }
    public function setSenha($senhaFront) {
        $this->senha = $senhaFront;
    }

    public function inserir() {
        //Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            //array com os dados que deverão vir do front
            $lista = array(
                "nome" => '0',
                "email" => '0',
                "usuario" => '0',
                "senha" => '0'
            );

            if(verificarParam($resultado, $lista) != 1) {
                //validar vindos de forma correta do frontesnd (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FontEnd.'];                
            }else {
                //Validar campos quanto ao tipo de ddos e tamanho (Helper)
                $retornoNome = validarDados($resultado->nome, 'string', true);
                $retornoEmail = validarDados($resultado->email, 'email', true);
                $retornoUsuario = validarDados($resultado->usuario, 'string', true);
                $retornoSenha = validarDados($resultado->senha, 'string', true);

                if($retornoNome['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoNome['codigoHelper'],
                                'campo' => 'Nome',
                                'msg' => $retornoNome['msg']];
                }

                if($retornoEmail['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoEmail['codigoHelper'],
                                'campo' => 'E-mail',
                                'msg' => $retornoEmail['msg']];
                }

                if($retornoUsuario['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoUsuario['codigoHelper'],
                                'campo' => 'Usuário',
                                'msg' => $retornoUsuario['msg']];
                }

                if($retornoSenha['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoSenha['codigoHelper'],
                                'campo' => 'Senha',
                                'msg' => $retornoSenha['msg']];
                }

                //Se não encontrar erros
                if (empty($erros)) {
                    //Fazendo os setters
                    $this->setNome($resultado->nome);
                    $this->setEmail($resultado->email);
                    $this->setUsuario($resultado->usuario);
                    $this->setSenha($resultado->senha);

                    //Realizo a instância da Model
                    $this->load->model('M_usuario');

                    //Atributo $retorno recebe array com informações da validação do acesso
                    $resBanco = $this->M_usuario->inserir($this->getNome(),
                                                        $this->getEmail(),
                                                        $this->getUsuario(),
                                                        $this->getSenha());

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;                        
                    } else {
                        //captuta erro do banco
                        $erros[] = [
                            'codigo' => $resBanco['codigo'],
                            'msg' => $resBanco['msg']
                        ];
                    }
                }

            }
        }catch (Exception $e) {
            $retorno = array('codigo' => 0,
                            'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ', $e->getMessage());
        }

        //Monta retorno único
        if ($sucesso == true) {
            $retorno = ['sucesso' =>$sucesso, 'codigo' => $resBanco['codigo'],
                        'msg' => $resBanco['msg']];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        //Retorno no formato JSON
        echo json_encode($retorno);
    }

   

    public function consultar(){
    //Atributos para controlar o status de nosso método
    $erros = [];
    $sucesso = false;

    try {
        $json = file_get_contents('php://input');
        $resultado = json_decode($json);

        //Lista de campos que deverão vir do Front
        $lista = array(
            "nome" => '0',
            "email" =>'0',
            "usuario"=> '0'
        );

        if (verificarParam($resultado, $lista) != 1) {
            // Validar vindos de forma correta do frontend (Helper)
            $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
        } else {
            // Validar campos quanto ao tipo de dado e tamanho (Helper)
            $retornoNome = validarDadosConsulta($resultado->nome, 'string');
            $retornoEmail = validarDadosConsulta($resultado->email, 'email');
            $retornoUsuario = validarDadosConsulta($resultado->usuario, 'string');
            
            if($retornoNome['codigoHelper'] != 0){
                $erros[] = ['codigo' => $retornoNome['codigoHelper'],
                            'campo' => 'Nome',
                            'msg' => $retornoNome['msg']
                ];
            }

    if($retornoEmail['codigoHelper'] != 0){
        $erros[] = ['codigo' => $retornoEmail['codigoHelper'],
                    'campo' => 'E-mail',
                    'msg' => $retornoEmail['msg']
        ];
    }

    if($retornoUsuario['codigoHelper'] != 0){
        $erros[] = ['codigo' => $retornoUsuario['codigoHelper'],
                    'campo' => 'Usuário',
                    'msg' => $retornoUsuario['msg']
        ];
    }

    if(empty($erros)){
        //Setar valores
        $this->setNome($resultado->nome);
        $this->setEmail($resultado->email);
        $this->setUsuario($resultado->usuario);
        
        //Instanciar o Model
        $this->load->model('M_usuario');

        //Consultar no banco
        $resBanco = $this->M_usuario->consultar($this->getNome(),
                                            $this->getEmail(),
                                            $this->getUsuario(),
            
        );

        if($resBanco['codigo']== 1){
            $sucesso = true;
        } else {
            $erros[] = [
                'codigo' => $resBanco['codigo'],
                'msg' => $resBanco['msg']
            ];
        }
    }
    }
    
    }catch (Exception $e) {
        $retorno = array('codigo' => 0,
            'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' .
            $e->getMessage());
    }

    // Monta retorno único
    if ($sucesso == true) {
        $retorno = ['sucesso' => $sucesso, 'codigo' => $resBanco['codigo'],
                    'msg' =>$resBanco['msg'],
                    'dados' => $resBanco['dados']];
        
    } else {
        $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
    }

    //Retorno no formato JSON
    echo json_encode($retorno);
}
    public function alterar(){
        //Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            //Array com os dados que deverão vir do Front
            $lista = array(
                "idUsuario" => '0',
                "nome" => '0',
                "email" => '0',
                "senha" => '0'
            );

            if (verificarParam($resultado, $lista) != 1) {
                // Validar vindos de forma correta do frontend (Helper
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            }else{
                //Pelo menos um dos três parâmetros precisam ter dados para acontecer a atualização
                if(trim($resultado->nome) == '' && trim($resultado->email) == '' && trim($resultado->senha)==''){
                    $erros[] = ['codigo' => 12,
                                'msg' => 'Pelo menos um parâmetro precisa ser passado para atualização'];
                }else{
                    // Validar campos quanto ao tipo de dado e tamanho (Helper)
                    $retornoIdUsuario = validarDados($resultado->idUsuario,'int');
                    $retornoNome      = validarDadosConsulta($resultado->nome,'string');
                    $retornoEmail     = validarDadosConsulta($resultado->email,'string');
                    $retornoSenha     = validarDadosConsulta($resultado->senha,'string');
                    
                    if($retornoIdUsuario['codigoHelper'] != 0){
                        $erros[] = ['codigo' => $retornoIdUsuario['codigoHelper'],
                                    'campo'  => 'ID Usuario',
                                    'msg'    => $retornoIdUsuario['msg']];
                    }
                    
                    if($retornoNome['codigoHelper'] != 0){
                        $erros[] = ['codigo' => $retornoNome['codigoHelper'],
                                    'campo'  => 'Nome',
                                    'msg'    => $retornoNome['msg']];
                    }

                    if($retornoEmail['codigoHelper'] != 0){
                        $erros[] = ['codigo' => $retornoEmail['codigoHelper'],
                                    'campo'  => 'E-mail',
                                    'msg'    => $retornoEmail['msg']];
                    }

                    if($retornoSenha['codigoHelper'] != 0){
                        $erros[] = ['codigo' => $retornoSenha['codigoHelper'],
                                    'campo'  => 'Senha',
                                    'msg'    => $retornoSenha['msg']];
                    }

                    //Se não encontrar erros
                    if (empty($erros)) {

                        //Fazendo os setters
                        $this->setIdUsuario($resultado->idUsuario);
                        $this->setNome($resultado->nome);
                        $this->setEmail($resultado->email);
                        $this->setSenha($resultado->senha);

                        $this->load->model("M_usuario");

                        //Atributo $retorno recebe array com informações
                        //da alteração dos dados
                        $resBanco = $this->M_usuario->alterar($this->getIdUsuario(),
                                                            $this->getNome(),
                                                            $this->getEmail(),
                                                            $this->getSenha());

                        if ($resBanco['codigo'] == 1) {
                            $sucesso = true;
                        } else {
                            // Captura erro do banco
                            $erros[] = [
                                'codigo' => $resBanco['codigo'],
                                'msg'    => $resBanco['msg']
                            ];
                        }
                    }

                }
            }
            }catch (Exception $e) {
                $retorno = array('codigo' => 0,
                                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' .
                                        $e->getMessage());
            }

            // Monta retorno único
            if ($sucesso == true) {
                $retorno = ['sucesso' => $sucesso, 'codigo' => $resBanco['codigo'],
                        'msg' => $resBanco['msg']];
            } else {
                $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
            }

            //Retorno no formato JSON
            echo json_encode($retorno);
            }

    public function desativar(){

        //Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            //Array com os dados que deverão vir do Front
            $lista = array(
                "idUsuario" => '0'
            );

            if (verificarParam($resultado, $lista) != 1) {
                // Validar vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            }else{
                // Validar campos quanto ao tipo de dado e tamanho (Helper)
                $retornoIdUsuario = validarDados($resultado->idUsuario,'int');

                if($retornoIdUsuario['codigoHelper'] != 0){
                    $erros[] = ['codigo' => $retornoIdUsuario['codigoHelper'],
                                'campo' => 'ID Usuário',
                                'msg'   => $retornoIdUsuario['msg']];
                }
                //Se não encontrar erros
                if (empty($erros)) {

                    //Fazendo os setters
                    $this->setIdUsuario($resultado->idUsuario);

                    //Realizo a instância da Model
                    $this->load->model('M_usuario');

                    //Atributo $retorno recebe array com informações
                    $resBanco = $this->M_usuario->desativar($this->getIdUsuario());

                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        // Captura erro do banco
                        $erros[] = [
                            'codigo' => $resBanco['codigo'],
                            'msg'    => $resBanco['msg']
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            $retorno = array('codigo' => 0,
                            'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ',
                                    $e->getMessage());
        }
        // Monta retorno único
        if ($sucesso == true) {
            $retorno = ['sucesso' => $sucesso, 'codigo' => $resBanco['codigo'],
                                'msg' => $resBanco['msg'] ];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        //Retorno no formato JSON
        echo json_encode($retorno);
    }

    public function logar() {
        //Atributos para controlar o status do nosso método
        $erros = [];
        $sucesso = false;

        try {

            //Usuário e senha recebidos via JSON
            //e colocados em atributos
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            //Array com os dados que deverão vir do Front
            $lista = array(
                "usuario" => '0',
                "senha" => '0'
            );

            if (verificarParam($resultado, $lista) != 1) {
                // Validar vindos de forma correta do frontend (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            }else{
                // Validar campos quanto ao tipo de dado e tamanho (Helper)
                $retornoUsuario = validarDados($resultado->usuario, 'string',true);
                $retornoSenha   = validarDados($resultado->senha, 'string',true);

                if($retornoUsuario['codigoHelper'] != 0){
                    $erros[] = ['codigo' => $retornoUsuario['codigoHelper'],
                                'campo' => 'Usuário',
                                'msg'   => $retornoUsuario['msg'] ];
                }

                if($retornoSenha['codigoHelper'] != 0){
                    $erros[] = ['codigo' => $retornoSenha['codigoHelper'],
                                'campo' => 'Senha',
                                'msg'   => $retornoSenha['msg'] ];
                }

                //Se não encontrar erros
                if (empty($erros)) {
                    
                    //Fazendo os setters
                    $this->setUsuario($resultado->usuario);
                    $this->setSenha($resultado->senha);

                    //Realizo a instância da Model
                    $this->load->model('M_usuario');

                    //Atributo $retorno recebe array com informações
                    //da validação do acesso
                    $resBanco = $this->M_usuario->validaLogin($this->getUsuario(),
                                                            $this->getSenha());
                    if ($resBanco['codigo'] == 1) {
                            $sucesso = true;
                    } else {
                        // Captura erro do banco
                        $erros[] = [
                            'codigo' => $resBanco['codigo'],
                            'msg'    => $resBanco['msg']
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            $retorno = array('codigo' => 0,
                            'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ',
                            $e->getMessage());
        }

        // Monta retorno único
        if ($sucesso == true) {
            $retorno = ['sucesso' => $sucesso, 'codigo' => $resBanco['codigo'],
                        'msg' => $resBanco['msg'] ];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

         //Retorno no formato JSON
         echo json_encode($retorno);
    }
}