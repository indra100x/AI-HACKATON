import {React, useState} from "react";
import { 
    X, Phone, Mail, MoveLeft, Menu, 
    Users, MessageCircle, Search, Crown, 
    Star, Award, BookOpen, Zap, Eye, 
    TrendingUp, Shield, Globe, Clock,
    CheckCircle, ArrowRight, Play,
    Mic, Camera, FileText, Heart,
    Settings, Bell, UserPlus
  } from "lucide-react";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faAndroid, faLinux,faWindows } from "@fortawesome/free-brands-svg-icons";
import { useNavigate } from "react-router-dom";
const Landing = () => {
    const navigate = useNavigate()
    const [isOpen, setIsOpen] = useState(false);
  
    const handleToggle = () => {
      setIsOpen(!isOpen);
    };
    const features = [
        {
          icon: Crown,
          title: "قعدة صفوة",
          description: "جلسات حوار فكري أسبوعية مباشرة مع نخبة من الخبراء والمفكرين",
          color: "purple"
        },
        {
          icon: Globe,
          title: "الدوائر الفكرية",
          description: "12+ دائرة متخصصة من تكنولوجيا إلى فلسفة، لكل مجال مجتمعه",
          color: "blue"
        },
        {
          icon: MessageCircle,
          title: "محادثات متقدمة",
          description: "نظام رسائل شامل مع دردشة فردية وجماعية ومشاركة الملفات",
          color: "green"
        },
        {
          icon: Search,
          title: "بحث ذكي",
          description: "بحث متقدم مع فلاتر ذكية للعثور على المحتوى والخبراء بسرعة",
          color: "indigo"
        },
        {
          icon: FileText,
          title: "محتوى متنوع",
          description: "مقالات، أسئلة، نقاشات، أخبار، وأفكار من أقلام متخصصة",
          color: "orange"
        },
        {
          icon: Award,
          title: "نظام الرتب",
          description: "7 رتب من مبتدئ إلى صفوة، كل رتبة لها صلاحيات ومميزات خاصة",
          color: "yellow"
        }
      ];
    return (
        <>
            <nav className="bg-white shadow-sm fixed w-full top-0 z-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div className="flex justify-between items-center">
                    <div className="w-20 cursor-pointer">
                        <p onClick={() => navigate("/")}>logo</p>    
                    </div>

                    {/* Desktop Menu */}
                    <div className="hidden md:block">
                        <div className="ml-10 flex items-baseline space-x-4 space-x-reverse">
                            <a href="#who-we-are" className="text-gray-600 hover:text-blue-600 px-3 py-2">who we are</a>
                            <a href="#how-it-works" className="text-gray-600 hover:text-blue-600 px-3 py-2">how it works</a>
                            <a href="#features" className="text-gray-600 hover:text-blue-600 px-3 py-2">features</a>
                            <a href="#ranks" className="text-gray-600 hover:text-blue-600 px-3 py-2">download</a>
                            <button 
                            onClick={() => navigate('/register')}
                            className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700"
                            >
                            join us
                            </button>
                        </div>
                    </div>

                    {/* Mobile menu button */}
                    <div className="md:hidden">
                    <button onClick={handleToggle} className="text-gray-600">
                        {isOpen ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
                    </button>
                    </div>
                </div>
                </div>

                {/* Mobile Menu */}
                {isOpen && (
                <div className="md:hidden bg-white border-t">
                    <div className="px-2 pt-2 pb-3 space-y-1">
                    <a href="#who-we-are" className="block px-3 py-2 hover:text-blue-600 text-gray-600">who we are</a>
                    <a href="#how-it-works" className="block px-3 py-2 hover:text-blue-600 text-gray-600">how it works</a>
                    <a href="#features" className="block px-3 py-2 hover:text-blue-600 text-gray-600">features</a>
                    <a href="#ranks" className="block px-3 py-2 hover:text-blue-600 text-gray-600">download</a>
                    <button 
                        onClick={() => navigate('/register')}
                        className="w-full text-left px-3 py-2 bg-blue-600 text-white rounded-lg"
                    >
                        join us
                    </button>
                    </div>
                </div>
                )}
            </nav>
            <section id="who-we-are" className="pt-20 pb-16 bg-gradient-to-br from-purple-50 via-blue-50 to-indigo-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="text-center">
                    <h1 className="text-4xl md:text-6xl font-bold text-gray-900 mb-6">
                    <span className="bg-gradient-to-r from-cyan-600 to-blue-600 bg-clip-text text-transparent">
                        our project
                    </span>
                    <br />
                    find the truth
                    </h1>
                    <p className="text-xl md:text-2xl text-gray-600 mb-8 max-w-3xl mx-auto leading-relaxed">
                        simple project to discover the deepfake videos and images
                    </p>
                    <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <button 
                        onClick={() => navigate('/register')}
                        className="bg-gradient-to-r to350% from-cyan-600 to-blue-600 text-white px-8 py-4 rounded-xl font-semibold text-lg hover:from-cyan-700 hover:to-blue-700 transform hover:scale-105 transition-all flex items-center gap-2"
                    >
                        <Crown className="w-5 h-5" />
                        JOIN US
                        <ArrowRight className="w-5 h-5" />
                    </button>
                    <button 
                        onClick={() => navigate('/login')}
                        className="border-2 border-blue-600 text-blue-600 px-8 py-4 rounded-xl font-semibold text-lg hover:bg-blue-600 hover:text-white transition-all"
                    >
                        LOGIN
                    </button>
                    </div>
                </div>
                </div>
            </section>
            <section id="how-it-works" className="py-16 bg-gradient-to-br from-purple-50 to-blue-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="text-center mb-16">
                    <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        HOW IT WORKS
                    </h2>
                    <p className="text-xl text-gray-600 max-w-2xl mx-auto">
                    simple 3 steps to use it
                    </p>
                </div>
                
                <div className="grid md:grid-cols-3 gap-8">
                    <div className="text-center">
                    <div className="bg-cyan-600 text-white w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-4">1</div>
                    <h3 className="font-semibold text-lg mb-2">create new account</h3>
                    </div>
                    <div className="text-center">
                    <div className="bg-blue-600 text-white w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-4">2</div>
                    <h3 className="font-semibold text-lg mb-2">upload image/video</h3>
                    </div>
                    <div className="text-center">
                    <div className="bg-indigo-600 text-white w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-4">3</div>
                    <h3 className="font-semibold text-lg mb-2">show the result</h3>
                    </div>
                </div>
                </div>
            </section>
            <section id="features" className="py-16 bg-gray-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="text-center mb-16">
                    <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    features make our project exceptional
                    </h2>
                </div>
                
                <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    {features.map((feature, index) => (
                    <div key={index} className="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div className={`bg-${feature.color}-100 p-3 rounded-lg w-fit mb-4`}>
                        <feature.icon className={`w-6 h-6 text-${feature.color}-600`} />
                        </div>
                        <h3 className="text-xl font-semibold text-gray-900 mb-3">{feature.title}</h3>
                        <p className="text-gray-600 leading-relaxed">{feature.description}</p>
                    </div>
                    ))}
                </div>
                </div>
            </section>
            {/* CTA Section */}
            <section className="py-16 bg-gradient-to-r to-60% from-cyan-600 to-blue-600">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 className="text-3xl md:text-4xl font-bold text-white mb-6">
                    SELECT YOUR OS
                </h2>
                <div className="flex flex-col sm:flex-row gap-4 justify-center">
                    <button className="p-5 bg-white rounded-xl hover:bg-cyan-200 cursor-pointer font-bold">
                        <FontAwesomeIcon icon={faAndroid} className="text-7xl"/>
                    </button>
                    <button className="p-5 bg-white rounded-xl hover:bg-cyan-200 cursor-pointer font-bold">
                        <FontAwesomeIcon icon={faLinux} className="text-7xl"/>
                    </button>
                    <button className="p-5 bg-white rounded-xl hover:bg-cyan-200 cursor-pointer font-bold">
                        <FontAwesomeIcon icon={faWindows} className="text-7xl"/>
                    </button>
                </div>
                </div>
            </section>
        </>
    )
}
export default Landing;