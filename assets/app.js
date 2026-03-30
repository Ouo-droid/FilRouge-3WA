import './styles/styles.scss';

fetch('http://localhost:8000/create').then(
    response => response.json()
).then(
    data => console.log(data)
);