insert into natureza_operacao (codigo, nome, local_servico) values ('1', 'Tributação no município', 'L');
insert into natureza_operacao (codigo, nome, local_servico) values ('2', 'Tributação fora do município', 'F');
insert into natureza_operacao (codigo, nome, local_servico) values ('3', 'Isenção', 'L');
insert into natureza_operacao (codigo, nome, local_servico) values ('4', 'Imune', 'L');
insert into natureza_operacao (codigo, nome, local_servico) values ('5', 'Exigibilidade suspensa por decisão judicial', 'L');
insert into natureza_operacao (codigo, nome, local_servico) values ('6', 'Exigibilidade suspensa por procedimento administrativo', 'L');
update natureza_operacao set padrao='N';
update natureza_operacao set padrao='Y' where codigo='1';
