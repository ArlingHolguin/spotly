
import React, {useEffect } from 'react';
import { Button, Form, Input, message, Space, Table } from 'antd';
import Service from '../services';
import { useHookLocalStorage } from '../helpers/useHookLocalStorage';
import { CopyOutlined, DeleteOutlined, EnterOutlined, LinkOutlined } from '@ant-design/icons';



const Shortener = () => {
    const [form] = Form.useForm();
    const service = new Service();
    const url = window.location.origin;


    // Usa localStorage para guardar la última URL acortada
    const [shortenedUrl, setShortenedUrl] = useHookLocalStorage('shortenedUrl', null);
    const [urlList, setUrlList] = useHookLocalStorage('urlList', []);

    const onFinish = async (values: { url: string }) => {
        try {
            const inputUrl = new URL(values.url);

            if (inputUrl.origin === url) {
                message.error('No puedes acortar URLs del mismo dominio.');
                return;
            }
            
            // Llamar al servicio para acortar la URL
            const response = await service.Shortener({ url: values.url });

            if (response) {
                message.success('URL acortada exitosamente.');
                form.resetFields(); // Limpiar el formulario
                setShortenedUrl(response.data); // Guardar la URL acortada en localStorage
            }
        } catch (error) {
            message.error('Ocurrió un error al acortar la URL.');
        }
    };

    const onFinishFailed = () => {
        console.log('Error al enviar el formulario');
    };

    const copyToClipboard = (shortCode: string) => {
        const fullUrl = `${window.location.origin}/${shortCode}`; // Construir la URL completa
        navigator.clipboard.writeText(fullUrl).then(
            () => message.success('¡URL copiada al portapapeles!'),
            (err) => message.error('No se pudo copiar la URL.')
        );
    };

    const fetchUrls = async () => {
        try {
            const response = await service.GetUrls(); // Ajusta el servicio según tu implementación
            if (response) setUrlList(response.data);
        } catch (error) {
            console.log('Error al cargar las URLs:', error);
        }
    };

    // eliminar url DeleteUrl
    const deleteUrl = async (short_code: string) => {
        try {
            const response = await service.DeleteUrl(short_code);
            if (response.code === 200) {
                message.success('URL eliminada exitosamente.');
                //elimina localstoraje de la url eliminada
                setShortenedUrl(null);
            }
            fetchUrls();
        } catch (error) {
            console.log('Error al eliminar la URL:', error);
        }
    };

    useEffect(() => {
        fetchUrls();
    }, [shortenedUrl]);


    const columns = [
        {
            title: 'ID',
            dataIndex: 'id',
            key: 'id',
        },
        {
            title: 'Short Code',
            dataIndex: 'short_code',
            key: 'short_code',
            render: (_:any, record: any) => (
                <a
                    href={record.original_url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-blue-500 underline"
                >
                    {record.short_code}
                </a>
            ),
        },
        {
            title: 'Original URL',
            dataIndex: 'original_url',
            key: 'original_url',
            render: (original_url: string) => (
                <div className='!w-36 md:!w-96 overflow-x-auto '>
                    <a
                        href={original_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-blue-500 md:inline-block overflow-hidden  whitespace-nowrap overflow-ellipsis"
                    >
                        {original_url}
                    </a>
                    

                </div>
            ),
        },
        {
            title: 'Acciones',
            key: 'actions',
            render: (text: any, record: any) => (
                <Space size="middle">
                    <Button
                        icon={<CopyOutlined />}
                        onClick={() => copyToClipboard(record.short_code)} // Copiar el enlace corto
                        type="text"
                        title="Copiar URL"
                    >
                        
                    </Button>

                    <Button
                        icon={<DeleteOutlined />}
                        onClick={() => deleteUrl(record.short_code)} // Eliminar la URL
                        type="text"
                        danger
                        title="Eliminar URL"
                    >
                    </Button>

                </Space>
            ),
        },
    ];

    return (
        <div className="flex flex-col justify-center items-center mt-48">
            <Form

                form={form}
                onFinish={onFinish}
                onFinishFailed={onFinishFailed}
                autoComplete="off"
                className='!w-full md:!w-[80%]'
            >
                <input type="hidden" name="honeypot" />
                <div className='w-full flex gap-3 items-center'>
                    <Form.Item className='w-full'
                        name="url"
                        label=""
                        rules={[
                            { required: true, message: 'Por favor ingresa una URL' },
                            { type: 'url', message: 'Debe ser una URL válida' },
                        ]}
                    >
                         
                        <Input prefix={<LinkOutlined style={{ color: 'rgba(0,0,0,.25)' }} />} 
                                suffix={<EnterOutlined className='border p-1 rounded shadow-md shadow-gray-200' style={{ color: 'rgba(0,0,0,.25)' }} />}                                
                            allowClear className='!w-full h-14' 
                            placeholder="Ingresa la URL para acortar" />
                    </Form.Item>
                    <Form.Item>
                        <Space>
                            <Button type="primary" htmlType="submit" className='h-14'>
                                Acortar
                            </Button>
                        </Space>
                    </Form.Item>

                </div>
            </Form>

            {/* Mostrar la URL acortada si existe */}
            {/* {shortenedUrl && (
                <div className="mt-4 flex items-center space-x-2">
                    <p>
                        URL Acortada:{' '}
                        <a
                            href={shortenedUrl.original_url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-blue-500 underline"
                        >
                            {`${window.location.origin}/${shortenedUrl.short_code}`}
                        </a>
                    </p>
                    <Button
                        icon={<CopyOutlined />}
                        onClick={() => copyToClipboard(shortenedUrl.short_code)} // Copiar el enlace corto
                        type="text"
                        title="Copiar URL"
                    />
                </div>
            )} */}

            {/* Mostrar la lista de URLs acortadas */}
            <div className="mt-8">
                <h2 className="text-lg font-semibold text-gray-700 mb-2 ml-4">Últimas urls Acortadas</h2>
                <Table
                className='w-full min-w-[300px] md:min-w-[850px] md:max-w-[850px]  '
                    dataSource={urlList}
                    columns={columns}
                    rowKey="id" // Usar `id` como clave única para cada fila
                    pagination={{ pageSize: 5 }} // Opcional: configurar el número de filas por página
                />
            </div>

        </div>
    );
};

export default Shortener;
