import { useEffect, useState } from 'react';
import apiMember from '../../API/apiMember';
import { toast } from 'react-toastify';
import './Update.css';
import Breadcrumb from '../../component/Member/Breadcrumb';

function UpdateMember() {
    let [input, SetInput] = useState({
        email: '',
        name: '',
        pass: '',
        phone: '',
        address: '',
        country: '',
        avatar_render: [],
        avatar_old: [],
        avatar_new: [],
    });
    let [country, SetCountry] = useState([]);
    let [err, SetErr] = useState({});

    function handleChangInputFile(e) {
        let files = Array.from(e.target.files);
        SetInput((states) => ({
            ...states,
            avatar_new: files,
            avatar_render: files
        }));
    }

    useEffect(() => {
        apiMember
            .get('/country')
            .then((res) => {
                SetCountry(res.data);
            })
            .catch((errors) => console.log(errors));
        getDataUser();
    }, []);

    function getDataUser() {
        apiMember.get('/user').then((res) => {
            let user = res.data.data;
            let avatarData = [];

            if (user.avatar) {
                try {
                    avatarData = JSON.parse(user.avatar);
                    if (!Array.isArray(avatarData)) {
                        avatarData = [user.avatar];
                    }
                } catch (e) {
                    avatarData = [user.avatar];
                }
            }

            SetInput({
                email: user.email,
                name: user.name,
                pass: '',
                phone: user.phone,
                address: user.address,
                country: user.id_country,
                avatar_old: avatarData,
                avatar_new: [],
                avatar_render: avatarData,
            });
        });
    }

    function changInput(e) {
        const name = e.target.name;
        const value = e.target.value;
        SetInput((states) => ({ ...states, [name]: value }));
    }

    function renderImage() {
        if (input.avatar_new.length > 0) {
            return input.avatar_new.map((file, index) => {
                const imgSrc = URL.createObjectURL(file);
                return (
                    <div key={index} className="avatar_list">
                        <img src={imgSrc} alt="avatar"></img>
                    </div>
                );
            });
        }

        return input.avatar_render.map((value, index) => {
            const imgSrc = value.startsWith('http') ? value : `http://localhost:8000/${value}`;
            return (
                <div key={index} className="avatar_list">
                    <img src={imgSrc} alt="avatar"></img>
                </div>
            );
        });
    }

    function checkInput(e) {
        e.preventDefault();
        const phoneRegex = /^(0|\+84)[0-9]{9}$/;
        let allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        let errAll = {};
        let check = true;

        if (input.name == '') {
            errAll.name = 'Vui lòng nhập name';
            check = false;
        }
        if (input.phone == '') {
            errAll.phone = 'Vui lòng nhập phone';
            check = false;
        } else {
            if (!phoneRegex.test(input.phone)) {
                errAll.phone = 'Vui lòng nhập phone đúng định dạng';
                check = false;
            }
        }
        if (input.pass == '') {
            errAll.pass = 'Vui lòng nhập password';
            check = false;
        }
        if (input.address == '') {
            errAll.address = 'Vui lòng nhập address';
            check = false;
        }
        if (input.country == '') {
            errAll.country = 'Vui lòng chọn country';
            check = false;
        }
        if (input.avatar_new.length > 0) {
            if (input.avatar_new.length > 3) {
                errAll.file = 'Chỉ chọn được tối đa 3 files';
                check = false;
            } else {
                input.avatar_new.forEach((file) => {
                    if (file.size > 1024 * 1024) {
                        errAll.file = 'Vui lòng chọn ảnh nhỏ hơn 1mb';
                        check = false;
                    }

                    if (!allowedTypes.includes(file.type)) {
                        errAll.file = 'Vui lòng chọn đúng định dạng files';
                        check = false;
                    }
                });
            }
        }

        if (!check) {
            SetErr(errAll);
        } else {
            let data = new FormData();
            data.append('name', input.name);
            data.append('password', input.pass);
            data.append('phone', input.phone);
            data.append('address', input.address);
            data.append('id_country', input.country);
            if (input.avatar_new.length > 0) {
                input.avatar_new.forEach((file) => {
                    data.append('avatar[]', file);
                });
            }

            apiMember
                .post('/user', data)
                .then((res) => {
                    SetErr({});
                    console.log(res);
                    toast.success('Update thành công');

                    const updatedUser = {
                        ...JSON.parse(localStorage.getItem('user')),
                        name: input.name,
                        phone: input.phone,
                        address: input.address,
                        avatar: res.data.data.avatar,
                    };
                    localStorage.setItem('user', JSON.stringify(updatedUser));

                    window.dispatchEvent(new Event('user-updated'));

                    getDataUser();
                })
                .catch((error) => {
                    if (error.response && error.response.data) {
                        const status = error.response.status;
                        if (status !== 401 && status !== 403) {
                            const message =
                                error.response.data?.error ||
                                error.response.data?.errors ||
                                error.response.data?.message ||
                                error.message;
                            if (typeof message === 'object' && message !== null) {
                                const keys = Object.keys(message);
                                if (keys.length > 0) {
                                    const firstKey = keys[0];
                                    toast.error('Lỗi khi cập nhập : ' + message[firstKey]);
                                }
                            } else {
                                toast.error('Lỗi khi cập nhập : ' + message);
                            }
                        }
                    } else {
                        console.error('Lỗi không xác định:', error);
                        toast.error('Lỗi không xác định');
                    }
                });
        }
    }

    return (
        <div>
            <Breadcrumb
                items={[{ label: 'Tài Khoản', path: '/member/account/update' }, { label: 'Cập nhật thông tin' }]}
            />
            <div className="register">
                <h3>Cập Nhật Thông Tin</h3>
                <form encType="multipart/form-data">
                    <label>Email</label>
                    <input name="email" type="text" readOnly value={input.email} />
                    <p></p>

                    <label>Full Name</label>
                    <input
                        name="name"
                        type="text"
                        placeholder="Nhập name"
                        value={input.name}
                        onChange={changInput}
                    ></input>
                    <p>{err.name}</p>

                    <label>Password</label>
                    <input
                        name="pass"
                        type="password"
                        placeholder="Nhập password"
                        onChange={changInput}
                        value={input.pass}
                    ></input>
                    <p>{err.pass}</p>

                    <label>Phone Number</label>
                    <input
                        name="phone"
                        type="text"
                        placeholder="Nhập phone"
                        onChange={changInput}
                        value={input.phone}
                    ></input>
                    <p>{err.phone}</p>

                    <label>Address</label>
                    <input
                        name="address"
                        type="text"
                        placeholder="Nhập address"
                        onChange={changInput}
                        value={input.address}
                    ></input>
                    <p>{err.address}</p>

                    <label>Country</label>
                    <select name="country" value={input.country} onChange={changInput}>
                        <option value="">---Chọn country---</option>
                        {country &&
                            country.map((value, index) => {
                                return (
                                    <option key={index} value={value.id}>
                                        {value.name}
                                    </option>
                                );
                            })}
                    </select>
                    <p>{err.country}</p>

                    <label>Avatar</label>
                    <input
                        name="avatar"
                        type="file"
                        placeholder="Nhập avatar"
                        multiple
                        onChange={(e) => handleChangInputFile(e)}
                    ></input>
                    <p>{err.file}</p>
                    <div className="avatar">{renderImage()}</div>
                    <button onClick={(e) => checkInput(e)}>Cập nhật thông tin</button>
                </form>
            </div>
        </div>
    );
}
export default UpdateMember;