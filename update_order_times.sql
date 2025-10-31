-- ============================================
-- 订单时间字段更新 SQL
-- 更新时间：2025/10/31 22:03:19
-- 更新记录数：218 条
-- ============================================
-- 
-- 说明：
-- 1. 本 SQL 只更新订单的时间字段（create_time, pay_time, delivery_time, sign_time）
-- 2. 不会影响其他数据，不会有主键冲突问题
-- 3. 建议执行前先备份数据库
-- 4. 可以安全地在生产环境执行
-- 
-- ============================================

-- 开始事务（可选，如果需要回滚的话）
-- START TRANSACTION;

UPDATE `order` SET create_time = '1759284822', pay_time = '0', delivery_time = '0', sign_time = '0' WHERE order_id = '1';
UPDATE `order` SET create_time = '1759285575', pay_time = '0', delivery_time = '0', sign_time = '0' WHERE order_id = '2';
UPDATE `order` SET create_time = '1759287932', pay_time = '0', delivery_time = '0', sign_time = '0' WHERE order_id = '3';
UPDATE `order` SET create_time = '1759290083', pay_time = '0', delivery_time = '0', sign_time = '0' WHERE order_id = '4';
UPDATE `order` SET create_time = '1759292002', pay_time = '1759292391', delivery_time = '0', sign_time = '0' WHERE order_id = '5';
UPDATE `order` SET create_time = '1759293589', pay_time = '1759293630', delivery_time = '1759462830', sign_time = '1759866030' WHERE order_id = '6';
UPDATE `order` SET create_time = '1759317945', pay_time = '0', delivery_time = '0', sign_time = '0' WHERE order_id = '7';
UPDATE `order` SET create_time = '1759368002', pay_time = '1759368444', delivery_time = '0', sign_time = '0' WHERE order_id = '8';
UPDATE `order` SET create_time = '1759385111', pay_time = '1759385632', delivery_time = '0', sign_time = '0' WHERE order_id = '9';
UPDATE `order` SET create_time = '1759385138', pay_time = '1759385298', delivery_time = '0', sign_time = '0' WHERE order_id = '10';
UPDATE `order` SET create_time = '1759391819', pay_time = '1759392343', delivery_time = '0', sign_time = '0' WHERE order_id = '11';
UPDATE `order` SET create_time = '1759394679', pay_time = '1759394810', delivery_time = '0', sign_time = '0' WHERE order_id = '12';
UPDATE `order` SET create_time = '1759400495', pay_time = '1759400791', delivery_time = '0', sign_time = '0' WHERE order_id = '13';
UPDATE `order` SET create_time = '1759454908', pay_time = '1759454963', delivery_time = '0', sign_time = '0' WHERE order_id = '14';
UPDATE `order` SET create_time = '1759459273', pay_time = '1759459495', delivery_time = '0', sign_time = '0' WHERE order_id = '15';
UPDATE `order` SET create_time = '1759476014', pay_time = '1759476216', delivery_time = '0', sign_time = '0' WHERE order_id = '16';
UPDATE `order` SET create_time = '1759476236', pay_time = '1759476779', delivery_time = '0', sign_time = '0' WHERE order_id = '17';
UPDATE `order` SET create_time = '1759480162', pay_time = '1759480712', delivery_time = '0', sign_time = '0' WHERE order_id = '18';
UPDATE `order` SET create_time = '1759484700', pay_time = '1759485203', delivery_time = '0', sign_time = '0' WHERE order_id = '19';
UPDATE `order` SET create_time = '1759486260', pay_time = '1759486366', delivery_time = '0', sign_time = '0' WHERE order_id = '20';
UPDATE `order` SET create_time = '1759489494', pay_time = '1759490036', delivery_time = '0', sign_time = '0' WHERE order_id = '21';
UPDATE `order` SET create_time = '1759540730', pay_time = '1759541028', delivery_time = '0', sign_time = '0' WHERE order_id = '22';
UPDATE `order` SET create_time = '1759541292', pay_time = '1759541315', delivery_time = '0', sign_time = '0' WHERE order_id = '23';
UPDATE `order` SET create_time = '1759553904', pay_time = '1759554033', delivery_time = '0', sign_time = '0' WHERE order_id = '24';
UPDATE `order` SET create_time = '1759555044', pay_time = '1759555140', delivery_time = '0', sign_time = '0' WHERE order_id = '25';
UPDATE `order` SET create_time = '1759558109', pay_time = '1759558613', delivery_time = '0', sign_time = '0' WHERE order_id = '26';
UPDATE `order` SET create_time = '1759567011', pay_time = '1759567365', delivery_time = '0', sign_time = '0' WHERE order_id = '27';
UPDATE `order` SET create_time = '1759628371', pay_time = '1759628884', delivery_time = '0', sign_time = '0' WHERE order_id = '28';
UPDATE `order` SET create_time = '1759645832', pay_time = '1759645982', delivery_time = '0', sign_time = '0' WHERE order_id = '29';
UPDATE `order` SET create_time = '1759648177', pay_time = '1759648262', delivery_time = '0', sign_time = '0' WHERE order_id = '30';
UPDATE `order` SET create_time = '1759656793', pay_time = '1759656942', delivery_time = '0', sign_time = '0' WHERE order_id = '31';
UPDATE `order` SET create_time = '1759715133', pay_time = '1759715643', delivery_time = '0', sign_time = '0' WHERE order_id = '32';
UPDATE `order` SET create_time = '1759716712', pay_time = '1759717242', delivery_time = '0', sign_time = '0' WHERE order_id = '33';
UPDATE `order` SET create_time = '1759742114', pay_time = '1759742187', delivery_time = '0', sign_time = '0' WHERE order_id = '34';
UPDATE `order` SET create_time = '1759744708', pay_time = '1759745051', delivery_time = '0', sign_time = '0' WHERE order_id = '35';
UPDATE `order` SET create_time = '1759818425', pay_time = '1759818622', delivery_time = '0', sign_time = '0' WHERE order_id = '36';
UPDATE `order` SET create_time = '1759836568', pay_time = '1759836669', delivery_time = '0', sign_time = '0' WHERE order_id = '37';
UPDATE `order` SET create_time = '1759836861', pay_time = '1759837391', delivery_time = '0', sign_time = '0' WHERE order_id = '38';
UPDATE `order` SET create_time = '1759894795', pay_time = '1759895120', delivery_time = '0', sign_time = '0' WHERE order_id = '39';
UPDATE `order` SET create_time = '1759895684', pay_time = '1759895913', delivery_time = '0', sign_time = '0' WHERE order_id = '40';
UPDATE `order` SET create_time = '1759898302', pay_time = '1759898483', delivery_time = '0', sign_time = '0' WHERE order_id = '41';
UPDATE `order` SET create_time = '1759899307', pay_time = '1759899541', delivery_time = '0', sign_time = '0' WHERE order_id = '42';
UPDATE `order` SET create_time = '1759913756', pay_time = '1759914137', delivery_time = '0', sign_time = '0' WHERE order_id = '43';
UPDATE `order` SET create_time = '1759923556', pay_time = '1759923731', delivery_time = '0', sign_time = '0' WHERE order_id = '44';
UPDATE `order` SET create_time = '1759978053', pay_time = '1759978638', delivery_time = '0', sign_time = '0' WHERE order_id = '45';
UPDATE `order` SET create_time = '1759978154', pay_time = '1759978402', delivery_time = '0', sign_time = '0' WHERE order_id = '46';
UPDATE `order` SET create_time = '1759979305', pay_time = '1759979553', delivery_time = '0', sign_time = '0' WHERE order_id = '47';
UPDATE `order` SET create_time = '1759987756', pay_time = '1759987842', delivery_time = '0', sign_time = '0' WHERE order_id = '48';
UPDATE `order` SET create_time = '1759991585', pay_time = '1759991936', delivery_time = '0', sign_time = '0' WHERE order_id = '49';
UPDATE `order` SET create_time = '1759991687', pay_time = '1759992095', delivery_time = '0', sign_time = '0' WHERE order_id = '50';
UPDATE `order` SET create_time = '1760003041', pay_time = '1760003290', delivery_time = '0', sign_time = '0' WHERE order_id = '51';
UPDATE `order` SET create_time = '1760059091', pay_time = '1760059273', delivery_time = '0', sign_time = '0' WHERE order_id = '52';
UPDATE `order` SET create_time = '1760069130', pay_time = '1760069257', delivery_time = '0', sign_time = '0' WHERE order_id = '53';
UPDATE `order` SET create_time = '1760076232', pay_time = '1760076412', delivery_time = '0', sign_time = '0' WHERE order_id = '54';
UPDATE `order` SET create_time = '1760088894', pay_time = '1760088987', delivery_time = '0', sign_time = '0' WHERE order_id = '55';
UPDATE `order` SET create_time = '1760146997', pay_time = '1760147564', delivery_time = '0', sign_time = '0' WHERE order_id = '56';
UPDATE `order` SET create_time = '1760155490', pay_time = '1760155707', delivery_time = '0', sign_time = '0' WHERE order_id = '57';
UPDATE `order` SET create_time = '1760167424', pay_time = '1760167538', delivery_time = '0', sign_time = '0' WHERE order_id = '58';
UPDATE `order` SET create_time = '1760167483', pay_time = '1760167571', delivery_time = '0', sign_time = '0' WHERE order_id = '59';
UPDATE `order` SET create_time = '1760168758', pay_time = '1760168939', delivery_time = '0', sign_time = '0' WHERE order_id = '60';
UPDATE `order` SET create_time = '1760178105', pay_time = '1760178469', delivery_time = '0', sign_time = '0' WHERE order_id = '61';
UPDATE `order` SET create_time = '1760183619', pay_time = '1760184156', delivery_time = '0', sign_time = '0' WHERE order_id = '62';
UPDATE `order` SET create_time = '1760234441', pay_time = '1760234970', delivery_time = '0', sign_time = '0' WHERE order_id = '63';
UPDATE `order` SET create_time = '1760241831', pay_time = '1760242409', delivery_time = '0', sign_time = '0' WHERE order_id = '64';
UPDATE `order` SET create_time = '1760245455', pay_time = '1760245874', delivery_time = '0', sign_time = '0' WHERE order_id = '65';
UPDATE `order` SET create_time = '1760254631', pay_time = '0', delivery_time = '0', sign_time = '0' WHERE order_id = '66';
UPDATE `order` SET create_time = '1760259354', pay_time = '1760259555', delivery_time = '0', sign_time = '0' WHERE order_id = '67';
UPDATE `order` SET create_time = '1760265228', pay_time = '1760265395', delivery_time = '0', sign_time = '0' WHERE order_id = '68';
UPDATE `order` SET create_time = '1760317696', pay_time = '1760317709', delivery_time = '0', sign_time = '0' WHERE order_id = '69';
UPDATE `order` SET create_time = '1760321453', pay_time = '1760321998', delivery_time = '0', sign_time = '0' WHERE order_id = '70';
UPDATE `order` SET create_time = '1760327299', pay_time = '1760327844', delivery_time = '0', sign_time = '0' WHERE order_id = '71';
UPDATE `order` SET create_time = '1760334730', pay_time = '1760334898', delivery_time = '0', sign_time = '0' WHERE order_id = '72';
UPDATE `order` SET create_time = '1760335664', pay_time = '1760336084', delivery_time = '0', sign_time = '0' WHERE order_id = '73';
UPDATE `order` SET create_time = '1760344388', pay_time = '1760344938', delivery_time = '0', sign_time = '0' WHERE order_id = '74';
UPDATE `order` SET create_time = '1760350007', pay_time = '1760350405', delivery_time = '0', sign_time = '0' WHERE order_id = '75';
UPDATE `order` SET create_time = '1760350261', pay_time = '1760350619', delivery_time = '0', sign_time = '0' WHERE order_id = '76';
UPDATE `order` SET create_time = '1760351347', pay_time = '1760351686', delivery_time = '0', sign_time = '0' WHERE order_id = '77';
UPDATE `order` SET create_time = '1760404376', pay_time = '1760404773', delivery_time = '0', sign_time = '0' WHERE order_id = '78';
UPDATE `order` SET create_time = '1760405335', pay_time = '1760405356', delivery_time = '0', sign_time = '0' WHERE order_id = '79';
UPDATE `order` SET create_time = '1760415023', pay_time = '1760415137', delivery_time = '0', sign_time = '0' WHERE order_id = '80';
UPDATE `order` SET create_time = '1760418776', pay_time = '1760419261', delivery_time = '0', sign_time = '0' WHERE order_id = '81';
UPDATE `order` SET create_time = '1760426954', pay_time = '1760427006', delivery_time = '0', sign_time = '0' WHERE order_id = '82';
UPDATE `order` SET create_time = '1760427051', pay_time = '1760427111', delivery_time = '0', sign_time = '0' WHERE order_id = '83';
UPDATE `order` SET create_time = '1760491072', pay_time = '1760491124', delivery_time = '0', sign_time = '0' WHERE order_id = '84';
UPDATE `order` SET create_time = '1760499710', pay_time = '1760499960', delivery_time = '0', sign_time = '0' WHERE order_id = '85';
UPDATE `order` SET create_time = '1760502859', pay_time = '1760503323', delivery_time = '0', sign_time = '0' WHERE order_id = '86';
UPDATE `order` SET create_time = '1760517245', pay_time = '1760517700', delivery_time = '0', sign_time = '0' WHERE order_id = '87';
UPDATE `order` SET create_time = '1760517828', pay_time = '1760517896', delivery_time = '0', sign_time = '0' WHERE order_id = '88';
UPDATE `order` SET create_time = '1760518260', pay_time = '1760518631', delivery_time = '0', sign_time = '0' WHERE order_id = '89';
UPDATE `order` SET create_time = '1760526099', pay_time = '1760526647', delivery_time = '0', sign_time = '0' WHERE order_id = '90';
UPDATE `order` SET create_time = '1760577361', pay_time = '1760577569', delivery_time = '0', sign_time = '0' WHERE order_id = '91';
UPDATE `order` SET create_time = '1760578598', pay_time = '1760579152', delivery_time = '0', sign_time = '0' WHERE order_id = '92';
UPDATE `order` SET create_time = '1760582455', pay_time = '1760582615', delivery_time = '0', sign_time = '0' WHERE order_id = '93';
UPDATE `order` SET create_time = '1760586485', pay_time = '0', delivery_time = '0', sign_time = '0' WHERE order_id = '94';
UPDATE `order` SET create_time = '1760587688', pay_time = '1760587921', delivery_time = '0', sign_time = '0' WHERE order_id = '95';
UPDATE `order` SET create_time = '1760599024', pay_time = '1760599568', delivery_time = '0', sign_time = '0' WHERE order_id = '96';
UPDATE `order` SET create_time = '1760601703', pay_time = '1760602254', delivery_time = '0', sign_time = '0' WHERE order_id = '97';
UPDATE `order` SET create_time = '1760604468', pay_time = '1760605002', delivery_time = '0', sign_time = '0' WHERE order_id = '98';
UPDATE `order` SET create_time = '1760606575', pay_time = '1760607122', delivery_time = '0', sign_time = '0' WHERE order_id = '99';
UPDATE `order` SET create_time = '1760610039', pay_time = '1760610346', delivery_time = '0', sign_time = '0' WHERE order_id = '100';
UPDATE `order` SET create_time = '1760669212', pay_time = '0', delivery_time = '0', sign_time = '0' WHERE order_id = '101';
UPDATE `order` SET create_time = '1760670096', pay_time = '1760670134', delivery_time = '0', sign_time = '0' WHERE order_id = '102';
UPDATE `order` SET create_time = '1760670657', pay_time = '1760671058', delivery_time = '0', sign_time = '0' WHERE order_id = '103';
UPDATE `order` SET create_time = '1760671883', pay_time = '1760672423', delivery_time = '0', sign_time = '0' WHERE order_id = '104';
UPDATE `order` SET create_time = '1760684986', pay_time = '1760685211', delivery_time = '0', sign_time = '0' WHERE order_id = '105';
UPDATE `order` SET create_time = '1760695445', pay_time = '1760695481', delivery_time = '0', sign_time = '0' WHERE order_id = '106';
UPDATE `order` SET create_time = '1760697448', pay_time = '1760698029', delivery_time = '0', sign_time = '0' WHERE order_id = '107';
UPDATE `order` SET create_time = '1760701789', pay_time = '1760702219', delivery_time = '0', sign_time = '0' WHERE order_id = '108';
UPDATE `order` SET create_time = '1760749576', pay_time = '1760749921', delivery_time = '0', sign_time = '0' WHERE order_id = '109';
UPDATE `order` SET create_time = '1760754252', pay_time = '1760754315', delivery_time = '0', sign_time = '0' WHERE order_id = '110';
UPDATE `order` SET create_time = '1760757415', pay_time = '1760757520', delivery_time = '0', sign_time = '0' WHERE order_id = '111';
UPDATE `order` SET create_time = '1760759807', pay_time = '1760760363', delivery_time = '0', sign_time = '0' WHERE order_id = '112';
UPDATE `order` SET create_time = '1760769483', pay_time = '1760769898', delivery_time = '0', sign_time = '0' WHERE order_id = '113';
UPDATE `order` SET create_time = '1760836644', pay_time = '1760836993', delivery_time = '0', sign_time = '0' WHERE order_id = '114';
UPDATE `order` SET create_time = '1760836836', pay_time = '1760836935', delivery_time = '0', sign_time = '0' WHERE order_id = '115';
UPDATE `order` SET create_time = '1760837731', pay_time = '1760837927', delivery_time = '0', sign_time = '0' WHERE order_id = '116';
UPDATE `order` SET create_time = '1760841711', pay_time = '1760842221', delivery_time = '0', sign_time = '0' WHERE order_id = '117';
UPDATE `order` SET create_time = '1760854400', pay_time = '1760854589', delivery_time = '0', sign_time = '0' WHERE order_id = '118';
UPDATE `order` SET create_time = '1760856036', pay_time = '1760856529', delivery_time = '0', sign_time = '0' WHERE order_id = '119';
UPDATE `order` SET create_time = '1760856132', pay_time = '1760856358', delivery_time = '0', sign_time = '0' WHERE order_id = '120';
UPDATE `order` SET create_time = '1760871745', pay_time = '1760871829', delivery_time = '0', sign_time = '0' WHERE order_id = '121';
UPDATE `order` SET create_time = '1760872869', pay_time = '1760872902', delivery_time = '0', sign_time = '0' WHERE order_id = '122';
UPDATE `order` SET create_time = '1760932158', pay_time = '1760932502', delivery_time = '0', sign_time = '0' WHERE order_id = '123';
UPDATE `order` SET create_time = '1760932814', pay_time = '1760933100', delivery_time = '0', sign_time = '0' WHERE order_id = '124';
UPDATE `order` SET create_time = '1760939210', pay_time = '1760939373', delivery_time = '0', sign_time = '0' WHERE order_id = '125';
UPDATE `order` SET create_time = '1760948970', pay_time = '1760949452', delivery_time = '0', sign_time = '0' WHERE order_id = '126';
UPDATE `order` SET create_time = '1760953523', pay_time = '1760953658', delivery_time = '0', sign_time = '0' WHERE order_id = '127';
UPDATE `order` SET create_time = '1760957893', pay_time = '1760958299', delivery_time = '0', sign_time = '0' WHERE order_id = '128';
UPDATE `order` SET create_time = '1760958215', pay_time = '1760958810', delivery_time = '0', sign_time = '0' WHERE order_id = '129';
UPDATE `order` SET create_time = '1760959059', pay_time = '1760959578', delivery_time = '0', sign_time = '0' WHERE order_id = '130';
UPDATE `order` SET create_time = '1761014078', pay_time = '1761014251', delivery_time = '0', sign_time = '0' WHERE order_id = '131';
UPDATE `order` SET create_time = '1761018798', pay_time = '1761018859', delivery_time = '0', sign_time = '0' WHERE order_id = '132';
UPDATE `order` SET create_time = '1761021969', pay_time = '1761022105', delivery_time = '0', sign_time = '0' WHERE order_id = '133';
UPDATE `order` SET create_time = '1761031820', pay_time = '1761032098', delivery_time = '0', sign_time = '0' WHERE order_id = '134';
UPDATE `order` SET create_time = '1761045159', pay_time = '1761045367', delivery_time = '0', sign_time = '0' WHERE order_id = '135';
UPDATE `order` SET create_time = '1761046082', pay_time = '1761046507', delivery_time = '0', sign_time = '0' WHERE order_id = '136';
UPDATE `order` SET create_time = '1761096276', pay_time = '1761096336', delivery_time = '0', sign_time = '0' WHERE order_id = '137';
UPDATE `order` SET create_time = '1761102993', pay_time = '1761103419', delivery_time = '0', sign_time = '0' WHERE order_id = '138';
UPDATE `order` SET create_time = '1761108093', pay_time = '1761108115', delivery_time = '0', sign_time = '0' WHERE order_id = '139';
UPDATE `order` SET create_time = '1761109115', pay_time = '1761109307', delivery_time = '0', sign_time = '0' WHERE order_id = '140';
UPDATE `order` SET create_time = '1761126150', pay_time = '1761126327', delivery_time = '0', sign_time = '0' WHERE order_id = '141';
UPDATE `order` SET create_time = '1761128576', pay_time = '1761128660', delivery_time = '0', sign_time = '0' WHERE order_id = '142';
UPDATE `order` SET create_time = '1761132688', pay_time = '1761133169', delivery_time = '0', sign_time = '0' WHERE order_id = '143';
UPDATE `order` SET create_time = '1761182321', pay_time = '1761182645', delivery_time = '0', sign_time = '0' WHERE order_id = '144';
UPDATE `order` SET create_time = '1761187904', pay_time = '1761188428', delivery_time = '0', sign_time = '0' WHERE order_id = '145';
UPDATE `order` SET create_time = '1761189504', pay_time = '1761189774', delivery_time = '0', sign_time = '0' WHERE order_id = '146';
UPDATE `order` SET create_time = '1761203218', pay_time = '1761203309', delivery_time = '0', sign_time = '0' WHERE order_id = '147';
UPDATE `order` SET create_time = '1761207926', pay_time = '1761208187', delivery_time = '0', sign_time = '0' WHERE order_id = '148';
UPDATE `order` SET create_time = '1761211694', pay_time = '1761211769', delivery_time = '0', sign_time = '0' WHERE order_id = '149';
UPDATE `order` SET create_time = '1761212381', pay_time = '1761212814', delivery_time = '0', sign_time = '0' WHERE order_id = '150';
UPDATE `order` SET create_time = '1761213255', pay_time = '1761213680', delivery_time = '0', sign_time = '0' WHERE order_id = '151';
UPDATE `order` SET create_time = '1761213315', pay_time = '1761213865', delivery_time = '0', sign_time = '0' WHERE order_id = '152';
UPDATE `order` SET create_time = '1761213918', pay_time = '1761214276', delivery_time = '0', sign_time = '0' WHERE order_id = '153';
UPDATE `order` SET create_time = '1761276390', pay_time = '1761276502', delivery_time = '0', sign_time = '0' WHERE order_id = '154';
UPDATE `order` SET create_time = '1761277958', pay_time = '1761278384', delivery_time = '0', sign_time = '0' WHERE order_id = '155';
UPDATE `order` SET create_time = '1761286494', pay_time = '1761286637', delivery_time = '0', sign_time = '0' WHERE order_id = '156';
UPDATE `order` SET create_time = '1761287450', pay_time = '1761287914', delivery_time = '0', sign_time = '0' WHERE order_id = '157';
UPDATE `order` SET create_time = '1761292367', pay_time = '1761292878', delivery_time = '0', sign_time = '0' WHERE order_id = '158';
UPDATE `order` SET create_time = '1761294212', pay_time = '1761294506', delivery_time = '0', sign_time = '0' WHERE order_id = '159';
UPDATE `order` SET create_time = '1761300458', pay_time = '1761300950', delivery_time = '0', sign_time = '0' WHERE order_id = '160';
UPDATE `order` SET create_time = '1761355797', pay_time = '1761356353', delivery_time = '0', sign_time = '0' WHERE order_id = '161';
UPDATE `order` SET create_time = '1761357750', pay_time = '1761358287', delivery_time = '0', sign_time = '0' WHERE order_id = '162';
UPDATE `order` SET create_time = '1761360102', pay_time = '1761360295', delivery_time = '0', sign_time = '0' WHERE order_id = '163';
UPDATE `order` SET create_time = '1761360879', pay_time = '1761360994', delivery_time = '0', sign_time = '0' WHERE order_id = '164';
UPDATE `order` SET create_time = '1761373974', pay_time = '1761374444', delivery_time = '0', sign_time = '0' WHERE order_id = '165';
UPDATE `order` SET create_time = '1761375945', pay_time = '1761376347', delivery_time = '0', sign_time = '0' WHERE order_id = '166';
UPDATE `order` SET create_time = '1761381526', pay_time = '1761381599', delivery_time = '0', sign_time = '0' WHERE order_id = '167';
UPDATE `order` SET create_time = '1761385060', pay_time = '1761385076', delivery_time = '0', sign_time = '0' WHERE order_id = '168';
UPDATE `order` SET create_time = '1761391673', pay_time = '1761392067', delivery_time = '0', sign_time = '0' WHERE order_id = '169';
UPDATE `order` SET create_time = '1761393289', pay_time = '1761393538', delivery_time = '0', sign_time = '0' WHERE order_id = '170';
UPDATE `order` SET create_time = '1761444977', pay_time = '1761445443', delivery_time = '0', sign_time = '0' WHERE order_id = '171';
UPDATE `order` SET create_time = '1761447345', pay_time = '1761447807', delivery_time = '0', sign_time = '0' WHERE order_id = '172';
UPDATE `order` SET create_time = '1761450256', pay_time = '1761450779', delivery_time = '0', sign_time = '0' WHERE order_id = '173';
UPDATE `order` SET create_time = '1761453854', pay_time = '1761454280', delivery_time = '0', sign_time = '0' WHERE order_id = '174';
UPDATE `order` SET create_time = '1761455848', pay_time = '1761456413', delivery_time = '0', sign_time = '0' WHERE order_id = '175';
UPDATE `order` SET create_time = '1761462944', pay_time = '1761463477', delivery_time = '0', sign_time = '0' WHERE order_id = '176';
UPDATE `order` SET create_time = '1761463357', pay_time = '1761463504', delivery_time = '0', sign_time = '0' WHERE order_id = '177';
UPDATE `order` SET create_time = '1761476980', pay_time = '1761477075', delivery_time = '0', sign_time = '0' WHERE order_id = '178';
UPDATE `order` SET create_time = '1761537862', pay_time = '1761538069', delivery_time = '0', sign_time = '0' WHERE order_id = '179';
UPDATE `order` SET create_time = '1761540875', pay_time = '1761541455', delivery_time = '0', sign_time = '0' WHERE order_id = '180';
UPDATE `order` SET create_time = '1761542182', pay_time = '1761542269', delivery_time = '0', sign_time = '0' WHERE order_id = '181';
UPDATE `order` SET create_time = '1761548091', pay_time = '1761548153', delivery_time = '0', sign_time = '0' WHERE order_id = '182';
UPDATE `order` SET create_time = '1761549731', pay_time = '1761549808', delivery_time = '0', sign_time = '0' WHERE order_id = '183';
UPDATE `order` SET create_time = '1761552198', pay_time = '1761552631', delivery_time = '0', sign_time = '0' WHERE order_id = '184';
UPDATE `order` SET create_time = '1761558391', pay_time = '1761558441', delivery_time = '0', sign_time = '0' WHERE order_id = '185';
UPDATE `order` SET create_time = '1761629740', pay_time = '1761630203', delivery_time = '0', sign_time = '0' WHERE order_id = '186';
UPDATE `order` SET create_time = '1761638148', pay_time = '1761638271', delivery_time = '0', sign_time = '0' WHERE order_id = '187';
UPDATE `order` SET create_time = '1761638386', pay_time = '1761638650', delivery_time = '0', sign_time = '0' WHERE order_id = '188';
UPDATE `order` SET create_time = '1761642364', pay_time = '1761642500', delivery_time = '0', sign_time = '0' WHERE order_id = '189';
UPDATE `order` SET create_time = '1761642501', pay_time = '1761642886', delivery_time = '0', sign_time = '0' WHERE order_id = '190';
UPDATE `order` SET create_time = '1761645291', pay_time = '1761645466', delivery_time = '0', sign_time = '0' WHERE order_id = '191';
UPDATE `order` SET create_time = '1761647294', pay_time = '1761647443', delivery_time = '0', sign_time = '0' WHERE order_id = '192';
UPDATE `order` SET create_time = '1761651572', pay_time = '1761652102', delivery_time = '0', sign_time = '0' WHERE order_id = '193';
UPDATE `order` SET create_time = '1761704904', pay_time = '1761705070', delivery_time = '0', sign_time = '0' WHERE order_id = '194';
UPDATE `order` SET create_time = '1761711573', pay_time = '1761711853', delivery_time = '0', sign_time = '0' WHERE order_id = '195';
UPDATE `order` SET create_time = '1761714610', pay_time = '1761714842', delivery_time = '0', sign_time = '0' WHERE order_id = '196';
UPDATE `order` SET create_time = '1761715077', pay_time = '1761715445', delivery_time = '0', sign_time = '0' WHERE order_id = '197';
UPDATE `order` SET create_time = '1761717439', pay_time = '1761717971', delivery_time = '0', sign_time = '0' WHERE order_id = '198';
UPDATE `order` SET create_time = '1761719349', pay_time = '1761719673', delivery_time = '0', sign_time = '0' WHERE order_id = '199';
UPDATE `order` SET create_time = '1761720564', pay_time = '1761720954', delivery_time = '0', sign_time = '0' WHERE order_id = '200';
UPDATE `order` SET create_time = '1761720836', pay_time = '1761721150', delivery_time = '0', sign_time = '0' WHERE order_id = '201';
UPDATE `order` SET create_time = '1761724709', pay_time = '1761725024', delivery_time = '0', sign_time = '0' WHERE order_id = '202';
UPDATE `order` SET create_time = '1761734090', pay_time = '1761734658', delivery_time = '0', sign_time = '0' WHERE order_id = '203';
UPDATE `order` SET create_time = '1761791563', pay_time = '1761792070', delivery_time = '0', sign_time = '0' WHERE order_id = '204';
UPDATE `order` SET create_time = '1761798622', pay_time = '1761799160', delivery_time = '0', sign_time = '0' WHERE order_id = '205';
UPDATE `order` SET create_time = '1761801786', pay_time = '1761801957', delivery_time = '0', sign_time = '0' WHERE order_id = '206';
UPDATE `order` SET create_time = '1761804402', pay_time = '1761804966', delivery_time = '0', sign_time = '0' WHERE order_id = '207';
UPDATE `order` SET create_time = '1761821697', pay_time = '1761821909', delivery_time = '0', sign_time = '0' WHERE order_id = '208';
UPDATE `order` SET create_time = '1761872853', pay_time = '1761873045', delivery_time = '0', sign_time = '0' WHERE order_id = '209';
UPDATE `order` SET create_time = '1761874345', pay_time = '1761874762', delivery_time = '0', sign_time = '0' WHERE order_id = '210';
UPDATE `order` SET create_time = '1761887106', pay_time = '1761887689', delivery_time = '0', sign_time = '0' WHERE order_id = '211';
UPDATE `order` SET create_time = '1761887373', pay_time = '1761887786', delivery_time = '0', sign_time = '0' WHERE order_id = '212';
UPDATE `order` SET create_time = '1761888804', pay_time = '1761889079', delivery_time = '0', sign_time = '0' WHERE order_id = '213';
UPDATE `order` SET create_time = '1761893384', pay_time = '1761893579', delivery_time = '0', sign_time = '0' WHERE order_id = '214';
UPDATE `order` SET create_time = '1761896060', pay_time = '1761896371', delivery_time = '0', sign_time = '0' WHERE order_id = '215';
UPDATE `order` SET create_time = '1761897589', pay_time = '1761897661', delivery_time = '0', sign_time = '0' WHERE order_id = '216';
UPDATE `order` SET create_time = '1761899520', pay_time = '1761899976', delivery_time = '0', sign_time = '0' WHERE order_id = '217';
UPDATE `order` SET create_time = '1761905244', pay_time = '1761905273', delivery_time = '0', sign_time = '0' WHERE order_id = '218';

-- 提交事务（如果开启了事务）
-- COMMIT;

-- 验证更新结果
SELECT 
    COUNT(*) as 总订单数,
    COUNT(CASE WHEN create_time >= 1759276800 AND create_time <= 1761868799 THEN 1 END) as 十月订单数,
    MIN(FROM_UNIXTIME(create_time)) as 最早订单时间,
    MAX(FROM_UNIXTIME(create_time)) as 最晚订单时间
FROM `order`;
