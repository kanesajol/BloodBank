import { NavigationContainer } from '@react-navigation/native';
import { ActivityIndicator, StyleSheet, View } from 'react-native';
import { StatusBar } from 'expo-status-bar';
import { useState } from 'react';
import { AuthProvider, useAuth } from './src/context/AuthContext';
import LandingScreen from './src/screens/LandingScreen';
import LoginScreen from './src/screens/LoginScreen';
import SignupScreen from './src/screens/SignupScreen';
import MainTabs from './src/navigation/MainTabs';

function RootNavigator() {
  const { user, booting } = useAuth();
  const [authScreen, setAuthScreen] = useState('landing');

  if (booting) {
    return (
      <View style={styles.boot}>
        <ActivityIndicator size="large" color="#990000" />
      </View>
    );
  }

  if (!user) {
    if (authScreen === 'login') {
      return <LoginScreen onBack={() => setAuthScreen('landing')} onSignUp={() => setAuthScreen('signup')} />;
    }

    if (authScreen === 'signup') {
      return <SignupScreen onBack={() => setAuthScreen('landing')} onLogin={() => setAuthScreen('login')} />;
    }

    return (
      <LandingScreen
        onLogin={() => setAuthScreen('login')}
        onSignUp={() => setAuthScreen('signup')}
      />
    );
  }

  return <MainTabs />;
}

export default function App() {
  return (
    <AuthProvider>
      <NavigationContainer>
        <RootNavigator />
        <StatusBar style="dark" backgroundColor="#ffffff" />
      </NavigationContainer>
    </AuthProvider>
  );
}

const styles = StyleSheet.create({
  boot: { flex: 1, alignItems: 'center', justifyContent: 'center', backgroundColor: '#fff' },
});
