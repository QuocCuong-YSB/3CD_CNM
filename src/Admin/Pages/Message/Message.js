import { useEffect, useState } from 'react';
import apiAdmin from '../../../API/apiAdmin';
import ChatAdmin from '../../../component/Admin/Chat/ChatAdmin';

function Message() {
    const [users, setUsers] = useState([]);

    let userId = localStorage.getItem('adminId');
    let token = localStorage.getItem('adminToken');

    useEffect(() => {
        apiAdmin.get('/member').then((res) => {
            setUsers(res.data.data);
        });
    }, []);
    return <ChatAdmin adminId={userId} token={token} members={users} />;
}
export default Message;
