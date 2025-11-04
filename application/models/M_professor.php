<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_professor extends CI_Model {
    /*
    Validação dos tipos de retornos nas validações (Codigo erro)
    0 - Erro de excessão
    1 - Operação realizada no banco com sucesso (Inserção, Alteração, Consulta ou Exclusão)
    8 - Houve algum problema de inserção, atualização, consulta ou exclusão
    9 - Professor desativado no sistema
    10 - Professor já cadastrado
    11 - Professor não encontrado pelo método publico
    98 - Método auxiliar de consulta que nãontrouxe dados
    */

    public function inserir ($nome, $cpf, $tipo) {
        try{
            //Verifica se o professor já está cadastrado
            $retornoConsulta = $this -> consultaProfessorCpf($cpf);

            if($retornoConsulta['codigo'] != 9 && $retornoConsulta['codigo'] != 10){
                //Query de inserção dos dados
                $this -> db -> query("insert into tbl_professor (nome, tipo, cpf)
                    values ('$nome', '$tipo', '$cpf')");

                //Verifica se a inserção ocorreu com sucesso
                if($this -> db -> affected_rows() > 0){
                    $dados = array('codigo' => 1, 'msg' => 'Professor cadastrado corretamente');
                } else {
                    $dados = array('codigo' => 8, 'msg' => 'Houve um problema na inserção de professor');
                }
            }else{
                $dados = array('codigo' => $retornoConsulta['codigo'], 'msg' => $retornoConsulta['msg']);
            }
        }catch(Exception $e){
            $dados = array(
                'codigo' => 00, 
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu '.$e->getMessage() 
            );
        }
        //Envia o array $dados com as informações tratadas
        //acima pela estrutura de decisão
        return $dados;  
    }

    private function consultaProfessorCpf($cpf){
        try{
            $sql = "select * from tbl_professor where cpf = '$cpf'";

            $retornoProfessor = $this -> db -> query($sql);

            //Verifica se a consulta ocorreu com sudesso
            if($retornoProfessor -> num_rows() > 0){
                $linha = $retornoProfessor -> row();
                if(trim($linha->estatus) =="D"){
                    $dados = array(
                        'codigo' => 9, 
                        'msg' => 'Professor desativado no sistema, caso precise reativar a mesma fale com o administrador.'
                    );
                } else {
                    $dados = array('codigo' => 10, 'msg' => 'Professor já cadastrado');
                }
            } else {
                $dados = array('codigo' => 98, 'msg' => 'Professor não encontrado');
            }
        }catch(Exception $e){
            $dados = array(
                'codigo' => 00, 
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu '.$e->getMessage() 
            );
        }
        //Envia o array $dados com as informações tratadas
        //acima pela estrutura de decisão
        return $dados;  
    }

    private function consultaProfessorCod ($codigo) {
        try{
            //Query para consultar dados de acordo com parâmetros passados
            $sql = "select * from tbl_professor where codigo = '$codigo' and estatus = ''";

            $retornoProfessor = $this -> db -> query($sql);

            //Verifica se a consulta ocorreu com sudesso
            if($retornoProfessor -> num_rows() > 0){
                $dados = array(
                    'codigo' => 1, 
                    'msg' => 'Consulta efetuada com sucesso.');

            } else {
                $dados = array(
                    'codigo' => 98, 
                    'msg' => 'Professor não encontrado');
            }
        }catch(Exception $e){
            $dados = array(
                'codigo' => 00, 
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu '.$e->getMessage() 
            );
        } 
        //Envia o array $ddos com as informações tratadas
        //acima pela estrutura de decisão if
        return $dados;  
    }

    public function consultar($codigo, $nome, $cpf, $tipo){
        try{
            //Query para consultar dados de acordo com parâmetros passados
            $sql = "select * from tbl_professor where estatus = ''";

            if($codigo != ""){
                $sql = " and codigo = '$codigo'";
            }

            if($nome != ""){
                $sql = " and nome like '%$nome%'";
            }

            if($cpf != ""){
                $sql = " and cpf = '$cpf'";
            }

            if($tipo != ""){
                $sql = " and tipo = '$tipo'";
            }

            $sql = $sql . " order by nome ";

            $retorno = $this -> db -> query($sql);

            //Verifica se a consulta ocorreu com sucesso
            if($retorno -> num_rows() > 0){
                $dados = array(
                    'codigo' => 1, 
                    'msg' => 'Consulta efetuada com sucesso.',
                    'dados' => $retorno -> result()
                );

            } else {
                $dados = array(
                    'codigo' => 11, 
                    'msg' => 'Professor não encontrado.');
            }
        }catch(Exception $e){
            $dados = array(
                'codigo' => 00, 
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu '.$e->getMessage() 
            );
        } 
        //Envia o array $ddos com as informações tratadas
        //acima pela estrutura de decisão if
        return $dados;  
    }

    public function alterar ($codigo, $nome, $cpf, $tipo) {
        try{
            //Verifica se o professor existe
            $retornoConsulta = $this -> consultaProfessorCod($codigo);

            if($retornoConsulta['codigo'] == 1){
                //Inicio a query para atualização
                $query = "update tbl_professor set ";

                //Vamos comparar os itens
                if($nome != ""){
                    $query .= " nome = '$nome',";
                }

                if($cpf != ""){
                    $query .= " cpf = '$cpf',";
                }

                if($tipo != ""){
                    $query .= " tipo = '$tipo',";
                }

                //Termino a concatenação da query
                $queryFinal = rtrim($query, ',') . " where codigo = '$codigo'";

                //Executa a query de atualização
                $this -> db -> query($queryFinal);

                //Verifica se a atualização ocorreu com sucesso
                if($this -> db -> affected_rows() > 0){
                    $dados = array('codigo' => 1, 'msg' => 'Professor atualizado corretamente');
                } else {
                    $dados = array('codigo' => 8, 'msg' => 'Houve algum problema na atualização da tabela de professor');
                }
            }else{
                $dados = array('codigo' => $retornoConsulta['codigo'],  
                                'msg' => $retornoConsulta['msg']);
            }
        }catch(Exception $e){
            $dados = array(
                'codigo' => 00, 
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu '.$e->getMessage() 
            );
        }
        //Envia o array $dados com as informações tratadas
        //acima pela estrutura de decisão if
        return $dados;
    }
   
    public function desativar ($codigo) {
        try{
            //Verifica se o professor existe
            $retornoConsulta = $this -> consultaProfessorCod($codigo);

            if($retornoConsulta['codigo'] == 1){
                //Query para desativar o professor
                $this -> db -> query("update tbl_professor set estatus = 'D' where codigo = '$codigo'");

                //Verifica se a desativação ocorreu com sucesso
                if($this -> db -> affected_rows() > 0){
                    $dados = array('codigo' => 1, 'msg' => 'Professor desativado corretamente');
                } else {
                    $dados = array('codigo' => 5, 'msg' => 'Houve algum problema na desativação do professor');
                }
            }else{
                $dados = array('codigo' => 6,  
                                'msg' => 'Professor não cadastrado no sistema, não pode excluir.');
            }
        }catch(Exception $e){
            $dados = array(
                'codigo' => 00, 
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu '.$e->getMessage() 
            );
        }
        //Envia o array $dados com as informações tratadas
        //acima pela estrutura de decisão if
        return $dados;  
    }   
    
}