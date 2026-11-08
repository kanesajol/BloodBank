import { NavigationContainer } from '@react-navigation/native';
import { ActivityIndicator, StyleSheet, View } from 'react-native';
import { StatusBar } from 'expo-status-bar';
import { AuthProvider, useAuth } from './src/context/AuthContext';
import LoginScreen from './src/screens/LoginScreen';
import MainTabs from './src/navigation/MainTabs';

function RootNavigator() {
  const { user, booting } = useAuth();

  if (booting) {
    return (
      <View style={styles.boot}>
        <ActivityIndicator size="large" color="#990000" />
      </View>
    );
  }

  if (!user) {
    return <LoginScreen />;
  }

  return <MainTabs />;
}

export default function App() {
  return (
    <AuthProvider>
      <NavigationContainer>
        <RootNavigator />
        <StatusBar style="dark" />
      </NavigationContainer>
    </AuthProvider>
  );
}

const styles = StyleSheet.create({
  boot: { flex: 1, alignItems: 'center', justifyContent: 'center', backgroundColor: '#fff' },
});
