<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Turma extends CI_Controller {

    /*
    Validação dos tipos de retornos nas validações (código de erro)
    1 - Operações realizada no banco de dados com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    2 - Conteúdo passado nulo ou vazio
    3 - Conteúdo zerado
    4 - Conteúdo não inteiro
    5 - Conteúdo não é um texto
    6 - Data em formato inválido
    12 - Na atualização, pelo menos um atributo deve ser passado
    99 - Parâmetros passados do front não correspondem ao metódo
    */

    //Atributos privados da classe
    private $codigo;
    private $descricao;
    private $capacidade;
    private $dataInicio;
    private $estatus;

    //Getters do atributos
    public function getCodigo() {
        return $this->codigo;
    }
    public function getDescricao() {
        return $this->descricao;
    }
    public function getCapacidade() {
        return $this->capacidade;
    }
    public function getDataInicio() {
        return $this->dataInicio;
    }
    public function getEstatus() {
        return $this->estatus;
    }

    //Setters dos atributos
    public function setCodigo($codigoFront) {
        $this->codigo = $codigoFront;
    }
    public function setDescricao($descricaoFront) {
        $this->descricao = $descricaoFront;
    }
    public function setCapacidade($capacidadeFront) {
        $this->capacidade = $capacidadeFront;
    }
    public function setDataInicio($dataInicioFront) {
        $this->dataInicio = $dataInicioFront;
    }
    public function setEstatus($estatusFront) {
        $this->tipoUsuario = $estatusFront;
    }
   
    

    public function inserir() {
        //Atributos para controlar o status de nosso método
       $erros = [];
       $sucesso = false;

       try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "descricao" => '0',
                "capacidade" => '0',
                "dataInicio" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                //Validar vindos de forma correta do frontEnd (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            }else {
                //Validar campos quanto ao tipo de dados e tamanho (Helper)
                $retornoDescricao = validarDados($resultado->descricao, 'string', true);
                $retornoCapacidade = validarDados($resultado->capacidade, 'int', true);
                $retornoDataInicio = validarDados($resultado->dataInicio, 'date', true);

                if($retornoDescricao['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoDescricao['codigoHelper'],
                                'campo' => 'descricao',
                                'msg' => $retornoDescricao['msg']];
                }
                if($retornoCapacidade['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoCapacidade['codigoHelper'],
                                'campo' => 'capacidade',
                                'msg' => $retornoCapacidade['msg']];
                }
                if($retornoDataInicio['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoDataInicio['codigoHelper'],
                                'campo' => 'dataInicio',
                                'msg' => $retornoDataInicio['msg']];
                }

                //Se não encontrar erros
                if (empty($erros)) {
                    $this->setDescricao($resultado->descricao);
                    $this->setCapacidade($resultado->capacidade);
                    $this->setDataInicio($resultado->dataInicio);
                          
                    $this->load->model('M_turma');
                    $resBanco = $this->M_turma->inserir(
                        $this->getDescricao(),
                        $this->getCapacidade(),
                        $this->getDataInicio()
                    );


                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;

                    }else{
                        //Captura erro de banco
                        $erros[] = [
                            'codigo' => $resBanco['codigo'], 
                            'msg' => $resBanco['msg']
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: '.$e->getMessage()];
        }

        //Montar o retorno unico
        if ($sucesso == true) {
            $retorno = ['sucesso' => $sucesso, 'codigo' => $resBanco['codigo'], 
                        'msg' => $resBanco['msg']];
        }else{
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        //Transforma o array em JSON
        echo json_encode($retorno);
    }

    public function consultar() {
        //Atributos para controlar o status de nosso método
       $erros = [];
       $sucesso = false;

       try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "codigo" => '0',
                "descricao" => '0',
                "capacidade" => '0',
                "dataInicio" => '0'
               
            ];

            if (verificarParam($resultado, $lista) != 1) {
                //Validar vindos de forma correta do frontEnd (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            }else{
                //Validar campos quanto ao tipo de dados e tamanho (Helper)
                $retornoCodigo = validarDadosConsulta($resultado->codigo, 'int');
                $retornoDescricao = validarDadosConsulta($resultado->descricao, 'string');
                $retornoCapacidade = validarDadosConsulta($resultado->capacidade, 'int');
                $retornoDataInicio = validarDadosConsulta($resultado->dataInicio, 'date');

                if($retornoCodigo['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoCodigo['codigoHelper'],
                                    'campo' => 'Codigo',
                                    'msg' => $retornoCodigo['msg']];
                }
                if($retornoDescricao['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoDescricao['codigoHelper'],
                                'campo' => 'Descricao',
                                'msg' => $retornoDescricao['msg']];
                }
                if($retornoCapacidade['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoCapacidade['codigoHelper'],
                                'campo' => 'Capacidade',
                                'msg' => $retornoCapacidade['msg']];
                }
                if($retornoDataInicio['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoDataInicio['codigoHelper'],
                                'campo' => 'DataInicio',
                                'msg' => $retornoDataInicio['msg']];
                }
                    
                    //Se não encontrar erros
                if (empty($erros)) {
                    $this->setCodigo($resultado->codigo);
                    $this->setDescricao($resultado->descricao);     
                    $this->setCapacidade($resultado->capacidade);
                    $this->setDataInicio($resultado->dataInicio);

                    $this->load->model('M_turma');
                    $resBanco = $this->M_turma->consultar($this->getCodigo(),
                                                             $this->getDescricao(),
                                                             $this->getCapacidade(),
                                                             $this->getDataInicio());
                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    }else {
                        //Captura erro de banco
                        $erros[] = [
                            'codigo' => $resBanco['codigo'], 
                            'msg' => $resBanco['msg']
                        ];
                    }
                }
            }
        } catch (Exception $e) {
                $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        //Montar o retorno unico
        if ($sucesso == true) {
            $retorno = ['sucesso' => $sucesso, 'codigo' => $resBanco['codigo'], 
                            'msg' => $resBanco['msg'], 'dados' => $resBanco['dados']];
        }else{
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        //Transforma o array em JSON
        echo json_encode($retorno);     
    }

    public function alterar() {
        //Atributos para controlar o status de nosso método
       $erros = [];
       $sucesso = false;

       try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "codigo" => '0',
                "descricao" => '0',
                "capacidade" => '0',
                "dataInicio" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                //Validar vindos de forma correta do frontEnd (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            }else{
                //Pelo menos um dos três parâmetros precisam ter dados para acontecer a atualização
                if (trim($resultado->descricao) == '' && trim($resultado->capacidade) == '' &&
                    trim($resultado->dataInicio) == '') {
                        $erros[] = ['codigo' => 12, 'msg' => 'Pelo menos um parãmetro precisa ser passado para atualização.'];
                }else {
                    //Validar campos quanto ao tipo de dados e tamanho (Helper)
                    $retornoCodigo = validarDados($resultado->codigo, 'int', true);
                    $retornoDescricao = validarDadosConsulta($resultado->descricao, 'string');      
                    $retornoCapacidade = validarDadosConsulta($resultado->capacidade, 'int');
                    $retornoDataInicio = validarDadosConsulta($resultado->dataInicio, 'date');

                    if($retornoCodigo['codigoHelper'] != 0) {
                        $erros[] = ['codigo' => $retornoCodigo['codigoHelper'],
                                        'campo' => 'codigo',
                                        'msg' => $retornoCodigo['msg']];
                    }
                    if($retornoDescricao['codigoHelper'] != 0) {
                        $erros[] = ['codigo' => $retornoDescricao['codigoHelper'],
                                    'campo' => 'descricao',
                                    'msg' => $retornoDescricao['msg']];
                    }
                    if($retornoCapacidade['codigoHelper'] != 0) {
                        $erros[] = ['codigo' => $retornoCapacidade['codigoHelper'],
                                    'campo' => 'capacidade',
                                    'msg' => $retornoCapacidade['msg']];
                    }
                    if($retornoDataInicio['codigoHelper'] != 0) {
                        $erros[] = ['codigo' => $retornoDataInicio['codigoHelper'],
                                    'campo' => 'dataInicio',
                                    'msg' => $retornoDataInicio['msg']];
                    }
                    //Se não encontrar erros
                    if (empty($erros)) {
                        $this->setCodigo($resultado->codigo);
                        $this->setDescricao($resultado->descricao);     
                        $this->setCapacidade($resultado->capacidade);
                        $this->setDataInicio($resultado->dataInicio);

                        $this->load->model('M_turma');
                        $resBanco = $this->M_turma->alterar($this->getCodigo(),
                                                             $this->getDescricao(),
                                                             $this->getCapacidade(),
                                                             $this->getDataInicio());
                        if ($resBanco['codigo'] == 1) {
                            $sucesso = true;
                        }else {
                            //Captura erro de banco
                            $erros[] = [
                                'codigo' => $resBanco['codigo'], 
                                'msg' => $resBanco['msg']
                            ];
                        }
                    }
                }
            }
        } catch (Exception $e) {
                $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        //Montar o retorno unico
        if ($sucesso == true) {
            $retorno = ['sucesso' => $sucesso, 'codigo' => $resBanco['codigo'], 
                            'msg' => $resBanco['msg']];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        //Transforma o array em JSON
        echo json_encode($retorno);
    }    
    

    public function desativar() {
        //Atributos para controlar o status de nosso método
        $erros = [];
        $sucesso = false;

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            $lista = [
                "codigo" => '0'
            ];

            if (verificarParam($resultado, $lista) != 1) {
                //Validar vindos de forma correta do frontEnd (Helper)
                $erros[] = ['codigo' => 99, 'msg' => 'Campos inexistentes ou incorretos no FrontEnd.'];
            } else {
                //Validar campos quanto ao tipo de dados e tamanho (Helper)
                $retornoCodigo = validarDados($resultado->codigo, 'int', true);

                if ($retornoCodigo['codigoHelper'] != 0) {
                    $erros[] = ['codigo' => $retornoCodigo['codigoHelper'],
                        'campo' => 'codigo',
                        'msg' => $retornoCodigo['msg']];
                }
                //Se não encontrar erros
                if (empty($erros)) {
                    $this->setCodigo($resultado->codigo);

                    $this->load->model('M_turma');
                    $resBanco = $this->M_turma->desativar($this->getCodigo());
                    
                    if ($resBanco['codigo'] == 1) {
                        $sucesso = true;
                    } else {
                        //Captura erro de banco
                        $erros[] = [
                            'codigo' => $resBanco['codigo'],
                            'msg' => $resBanco['msg']
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            $erros[] = ['codigo' => 0, 'msg' => 'Erro inesperado: ' . $e->getMessage()];
        }

        //Monta retorno único
        if ($sucesso == true) {
            $retorno = ['sucesso' => $sucesso, 'codigo' => $resBanco['codigo'],
                'msg' => $resBanco['msg']];
        } else {
            $retorno = ['sucesso' => $sucesso, 'erros' => $erros];
        }

        //Transforma o array em JSON
        echo json_encode($retorno);
    }
}
?>                 