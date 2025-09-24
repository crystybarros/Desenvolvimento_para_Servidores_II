<?php
defined('BASEPATH') or exit('No dirct script access alowed');

class M_sala extends CI_Model
{
	/*
	Validação dos tipos de retornos nas validações (Código de erro)
	0 - Erro de exceção
	1 - Operação realizada no banco de dados com sucesso (iserção, alteração, consulta ou exclusão
	8 - Houve algum problema de inserção, atualização, consulta ou exclusão
	9 - Sala já cadastrada
	98 - Método auxiliar de consulta que não trouxe dados
	*/
	
	public function inserir($codigo, $descricao, $andar, $capacidade){
		try{
			//verifica se a sala já está cadastrada
			$retornoConsulta = $this->consultaSala($codigo);
			
			if ($retornoConsulta['codigo'] != 9 &&
				$retornoConsulta['codigo'] != 10) {
				//Query deinserção dos dados
				$this->db->query("insert into tbl_sala (codigo, descricao, andar, capacidade)
					values ($codigo, '$descricao', $andar, $capacidade)");

				//verificar se a inserção ocorreu com sucesso
				if ($this->db->affected_rows() > 0){
					$dados = array(
						'codigo' => 1,
						'msg' => 'Sala cadastrada corretamente'
					);
				}else {
					$dados = array(
						'codigo' => 8,
						'msg' => 'Houve algum problema na inserção na tabela de salas.'
					);
				}
			} else {
				$dados = array('codigo' => $retornoConsulta ['codigo'],
					'msg' => $retornoConsulta ['msg']);
			}
		} catch (Exception $e) {
			$dados = array(
				'codigo' => 0,
				'msg' => 'ATENÇÃO: o seguinte erro aconteceu -> ' . $e->getMessage()
			);
		}

		//Envia o array $dados com as informações tratadas
		//acima pela estrutura de decisão if
		return $dados;
	}

	//Métido privado, pois será auxiliar nesta classe
	private function consultaSala($codigo){
		try {
			//Query para consultar dados de acordo com parâmetro passados
			$sql = "select * from tbl_sala where codigo = $codigo ";
			$retornoSala = $this->db->query($sql);

			//verifica se a consulta ocorreu com sucesso
			if ($retornoSala->num_rows() > 0) {
				$linha = $retornoSala->row();
				if (trim($linha->estatus) == "D") {

					$dados = array(
						'codigo'  => 9,
						'msg' => 'Sala desativada no sistema, caso precise reativar a mesma, fale com o administrador.'
					);

				} else {
					$dados = array(
						'codigo' => 10,
						'msg' => 'Sala já cadastrada no sistema.'
					);
				}
			} else {
				$dados = array(
					'codigo' => 98,
					'msg' => 'Sala não encontrada.'
				);
			}
		} catch (Exception $e) {
			$dados = array(
				'codigo' => 0,
				'msg' => 'ATENÇÃO: o seguinte erro aconteceu -> ' . $e->getMessage()
			);
		}
		//Envia o array $dados com as informações tratadas
		//acima pela estrutura de decisão if
		return $dados;
	}

		//Métido privado, pois será auxiliar nesta classe
	public function consultar($codigo, $descricao, $andar, $capacidade){
		try {
			//Query para consultar dados de acordo com parâmetro passados
			$sql = "select * from tbl_sala where estatus = '' ";

			if (trim ($codigo) != '') {
				$sql = $sql . " and codigo = $codigo ";
			}

			if (trim ($andar) != '') {
				$sql = $sql . " and andar = '$andar' ";
			}

			if (trim ($descricao) != '') {
				$sql = $sql . " and descricao like '%$descricao%' ";
			}

			if (trim ($capacidade) != '') {
				$sql = $sql . " and capacidade = $capacidade ";
			}

			$sql = $sql . " order by codigo";

			$retorno = $this ->db->query($sql);

			//verifica se a consulta ocorreu com sucesso
			if ($retorno->num_rows() > 0) {
				$dados = array (
					'codigo' =>1,
					'msg' => 'Consulta efetuada com sucesso.',
					'dados' => $retorno->result()
				);
			} else {
				$dados = array(
					'codigo' => 11,
					'msg' => 'Sala não encontrada.'
				);
			}
		} catch (Exception $e) {
			$dados = array(
				'codigo' => 00,
				'msg' => 'ATENÇÃO: o seguinte erro aconteceu -> ' . $e->getMessage()
			);
		}
		//Envia o array $dados com as informações tratadas
		//acima pela estrutura de decisão if
		return $dados;
	}


	public function alterar($codigo, $descricao, $andar, $capacidade){
			try {
			//verifica se a sala já está cadastrada
			$retornoConsulta = $this->consultaSala($codigo);
			
			if ($retornoConsulta['codigo'] == 10) {
				//inicio a query para atualização
				$query = "update tbl_sala set ";
				
				//vamos comparar os itens
			if ($descricao !== '') {
				$query.= "descricao = '$descricao', ";
			}

			if ($andar !== '') {
				$query.= "andar = '$andar', ";
			}

			if ($capacidade !== '') {
				$query.= "capacidade = '$capacidade', ";
			}

			//termino a concatenção da query
			$queryFinal = rtrim($query, ", ") . " where codigo = $codigo";

			//executo a query de atualização dos dados
			$this->db->query ($queryFinal);

			//verifica se a atualização ocorreu com sucesso
			if ($this->db->affected_rows() > 0) {
				$dados = array(
					'codigo' => 1,
					'msg' => 'Sala atualizada corretamente.'
				);
			} else {
				$dados = array(
					'codigo' => 8,
					'msg' => 'Houve algum problema na atualização na tabela de sala.'
				);
			}
			} else {
				$dados = array ('codigo' => $retornoConsulta ['codigo'],
						'msg' => $retornoConsulta ['msg']);
			}
		} catch (Exception $e) {
			$dados = array(
				'codigo' => 00,
				'msg' => 'ATENÇÃO: o seguinte erro aconteceu -> ' . $e->getMessage()
			);
		}
		//envia o array $dados com as informações tratadas
		//acima pela estrutura de decisão if
		return $dados;
	}


	public function desativar($codigo) {
		try {
			//verifica se a sala já está cadastrada
			$retornoConsulta = $this->consultaSala($codigo);

			if ($retornoConsulta ['codigo'] == 10) {
				//query de atualização dos dados
				$this->db->query("update tbl_sala set estatus = 'D' where codigo = $codigo");
				
				//verifica se a atualização ocorreu com sucesso
				if ($this->db->affected_rows() > 0) {
					$dados = array(
						'codigo' => 1,
						'msg' => 'Sala DESATIVADA corretamente.'
					);
				} else {
					$dados = array(
						'codigo' => 8,
						'msg' => 'Houve algum problema na DESATIVAÇÃO da sala.'
					);
				}
			} else {
				$dados = array ('codigo' => $retornoConsulta ['codigo'],
						'msg' => $retornoConsulta ['msg']);
			}

		} catch (Exception $e) {
			$dados = array(
				'codigo' => 00,
				'msg' => 'ATENÇÃO: o seguinte erro aconteceu -> ' . $e->getMessage()
			);
		}
		//envia o array $dados com as informações tratadas
		//acima pela estrutura de decisão if
		return $dados;
	}
}
?>