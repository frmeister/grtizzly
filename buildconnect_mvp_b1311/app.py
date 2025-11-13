# app.py
from flask import Flask, render_template, request, jsonify, redirect, url_for
from flask_sqlalchemy import SQLAlchemy
from flask_login import LoginManager, UserMixin, login_user, logout_user, login_required, current_user
from werkzeug.security import generate_password_hash, check_password_hash
from datetime import datetime
import os

# Создаем экземпляр Flask
app = Flask(__name__)

# Базовая конфигурация (без отдельного config.py)
app.config['SECRET_KEY'] = 'dev-secret-key-change-in-production'
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///timejobs.db'
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

# Инициализируем расширения
db = SQLAlchemy(app)
login_manager = LoginManager(app)
login_manager.login_view = 'login'

# Модели базы данных
class User(UserMixin, db.Model):
    __tablename__ = 'users'
    id = db.Column(db.Integer, primary_key=True)
    role = db.Column(db.String(20), nullable=False, default='worker')
    name = db.Column(db.String(100), nullable=False)
    email = db.Column(db.String(120), unique=True, nullable=False)
    password_hash = db.Column(db.String(200), nullable=False)
    phone = db.Column(db.String(20))
    avatar = db.Column(db.String(200))
    education = db.Column(db.Text)
    exp_years = db.Column(db.Integer, default=0)
    rating = db.Column(db.Float, default=0.0)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    
    def set_password(self, password):
        self.password_hash = generate_password_hash(password)

    def check_password(self, password):
        return check_password_hash(self.password_hash, password)

class Job(db.Model):
    __tablename__ = 'jobs'
    id = db.Column(db.Integer, primary_key=True)
    employer_id = db.Column(db.Integer, db.ForeignKey('users.id'))
    title = db.Column(db.String(200), nullable=False)
    description = db.Column(db.Text)
    city = db.Column(db.String(100))
    specialization = db.Column(db.String(100))
    wage = db.Column(db.Float, default=0.0)
    pay_type = db.Column(db.String(20), default='hourly')
    duration_days = db.Column(db.Integer, default=1)
    status = db.Column(db.String(20), default='approved')  # Для теста сразу approved
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

class Application(db.Model):
    __tablename__ = 'applications'
    id = db.Column(db.Integer, primary_key=True)
    job_id = db.Column(db.Integer, db.ForeignKey('jobs.id'))
    worker_id = db.Column(db.Integer, db.ForeignKey('users.id'))
    note = db.Column(db.Text)
    status = db.Column(db.String(20), default='applied')
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

@login_manager.user_loader
def load_user(user_id):
    return User.query.get(int(user_id))

# Маршруты
@app.route('/')
def index():
    jobs = Job.query.order_by(Job.created_at.desc()).limit(6).all()
    return render_template('index.html', jobs=jobs)

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        email = request.form.get('email')
        password = request.form.get('password')
        user = User.query.filter_by(email=email).first()
        
        if user and user.check_password(password):
            login_user(user)
            return redirect(url_for('index'))
        
        return render_template('auth/login.html', error='Неверные данные')
    
    return render_template('auth/login.html')

@app.route('/register', methods=['GET', 'POST'])
def register():
    if request.method == 'POST':
        name = request.form.get('name')
        email = request.form.get('email')
        password = request.form.get('password')
        role = request.form.get('role', 'worker')
        
        if User.query.filter_by(email=email).first():
            return render_template('auth/register.html', error='Email уже используется')
        
        user = User(name=name, email=email, role=role)
        user.set_password(password)
        
        db.session.add(user)
        db.session.commit()
        
        login_user(user)
        return redirect(url_for('index'))
    
    return render_template('auth/register.html')

@app.route('/logout')
@login_required
def logout():
    logout_user()
    return redirect(url_for('index'))

@app.route('/vacancies')
def vacancies():
    jobs = Job.query.order_by(Job.created_at.desc()).all()
    return render_template('vacancies/list.html', jobs=jobs)

@app.route('/vacancies/create', methods=['GET', 'POST'])
@login_required
def create_vacancy():
    if current_user.role != 'employer':
        return redirect(url_for('index'))
    
    if request.method == 'POST':
        job = Job(
            employer_id=current_user.id,
            title=request.form.get('title'),
            description=request.form.get('description'),
            city=request.form.get('city'),
            specialization=request.form.get('specialization'),
            wage=float(request.form.get('wage', 0)),
            pay_type=request.form.get('pay_type', 'hourly'),
            duration_days=int(request.form.get('duration_days', 1))
        )
        db.session.add(job)
        db.session.commit()
        return redirect(url_for('vacancies'))
    
    return render_template('vacancies/create.html')

@app.route('/profile')
@login_required
def profile():
    return render_template('profile/index.html')

# Создаем тестовые данные
def create_sample_data():
    # Проверяем, есть ли уже пользователи
    if not User.query.first():
        # Создаем тестового пользователя
        user = User(
            name='Тестовый Пользователь',
            email='test@example.com',
            role='worker'
        )
        user.set_password('123456')
        db.session.add(user)
        db.session.commit()
        print("Создан тестовый пользователь: test@example.com / 123456")

    # Создаем тестовые вакансии
    if not Job.query.first():
        jobs = [
            Job(
                employer_id=1,
                title='Разнорабочий на стройку',
                description='Требуется разнорабочий для помощи на строительном объекте. Работа в команде, ответственность, физическая выносливость.',
                city='Москва',
                specialization='Строительство',
                wage=1500,
                pay_type='hourly',
                duration_days=30
            ),
            Job(
                employer_id=1,
                title='Маляр-штукатур',
                description='Нужен опытный маляр для отделочных работ в новостройке. Опыт работы от 1 года, знание современных материалов.',
                city='Санкт-Петербург',
                specialization='Отделочные работы',
                wage=2000,
                pay_type='hourly', 
                duration_days=45
            ),
            Job(
                employer_id=1,
                title='Укладчик плитки',
                description='Требуется специалист по укладке керамической плитки. Собственные инструменты приветствуются, аккуратность, внимание к деталям.',
                city='Казань',
                specialization='Плиточные работы',
                wage=1800,
                pay_type='hourly',
                duration_days=20
            ),
            Job(
                employer_id=1,
                title='Электрик',
                description='Требуется электрик для монтажа электропроводки в жилом комплексе. Наличие допуска, опыт работы обязателен.',
                city='Новосибирск',
                specialization='Электромонтаж',
                wage=2200,
                pay_type='hourly',
                duration_days=60
            ),
            Job(
                employer_id=1,
                title='Сантехник',
                description='Нужен сантехник для установки сантехнического оборудования. Опыт работы с современным оборудованием.',
                city='Екатеринбург',
                specialization='Сантехнические работы',
                wage=1900,
                pay_type='hourly',
                duration_days=25
            ),
            Job(
                employer_id=1,
                title='Плотник',
                description='Требуется плотник для изготовления и установки деревянных конструкций. Опыт работы с деревом, знание пород древесины.',
                city='Краснодар',
                specialization='Плотницкие работы',
                wage=1700,
                pay_type='hourly',
                duration_days=35
            )
        ]
        db.session.add_all(jobs)
        db.session.commit()
        print("Создано 6 тестовых вакансий")

if __name__ == '__main__':
    with app.app_context():
        # Создаем таблицы в базе данных
        db.create_all()
        # Создаем тестовые данные
        create_sample_data()
    
    print("=" * 50)
    print("Сервер Time Jobs запущен!")
    print("Доступен по адресу: http://localhost:5000")
    print("Тестовый пользователь: test@example.com / 123456")
    print("=" * 50)
    
    app.run(debug=True, port=5000)